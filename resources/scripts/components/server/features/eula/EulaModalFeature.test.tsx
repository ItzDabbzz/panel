import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { SocketEvent, SocketRequest } from '@/components/server/events';
import EulaModalFeature from './EulaModalFeature';

const mocks = vi.hoisted(() => ({
    put: vi.fn(),
    post: vi.fn(),
    send: vi.fn(),
    error: vi.fn(),
    consoleOutput: (_message: string) => {},
}));
vi.mock('@/api/http', () => ({ default: { put: mocks.put, post: mocks.post } }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({
                server: { data: { uuid: 'server-uuid' } },
                status: { value: 'offline' },
                socket: {
                    connected: true,
                    instance: {
                        addListener: (event: string, callback: (message: string) => void) => {
                            if (event === SocketEvent.CONSOLE_OUTPUT) mocks.consoleOutput = callback;
                        },
                        removeListener: vi.fn(),
                        send: mocks.send,
                    },
                },
            }),
    },
}));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: mocks.error }),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({ visible, children }: { visible: boolean; children: ReactNode }) => (visible ? children : null),
}));

let container: HTMLDivElement;
let root: Root;

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.put.mockReset().mockResolvedValue(undefined);
    mocks.post.mockReset().mockRejectedValue(new Error('file.create denied'));
    mocks.send.mockReset();
    mocks.error.mockReset();
    container = document.createElement('div');
    root = createRoot(container);
    await act(async () => root.render(<EulaModalFeature />));
    await act(async () => mocks.consoleOutput('You need to agree to the EULA in order to run the server'));
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.restoreAllMocks();
});

const accept = () =>
    Array.from(container.querySelectorAll('button')).find((button) => button.textContent === 'I Accept')!;

describe('EULA acceptance', () => {
    it('updates the existing file before restarting without using the create endpoint', async () => {
        let finishSave!: () => void;
        mocks.put.mockImplementation(
            () =>
                new Promise<void>((resolve) => {
                    finishSave = resolve;
                }),
        );

        await act(async () => accept().click());
        expect(mocks.put).toHaveBeenCalledWith('/api/client/servers/server-uuid/files/write', 'eula=true', {
            params: { file: 'eula.txt' },
            headers: { 'Content-Type': 'text/plain' },
        });
        expect(mocks.post).not.toHaveBeenCalled();
        expect(mocks.send).not.toHaveBeenCalled();
        expect(accept()).toBeDefined();

        await act(async () => finishSave());
        expect(mocks.send).toHaveBeenCalledWith(SocketRequest.SET_STATE, 'restart');
        expect(accept()).toBeUndefined();
    });

    it('keeps the modal open and does not restart when updating fails', async () => {
        const error = new Error('file.update denied');
        mocks.put.mockRejectedValue(error);
        vi.spyOn(console, 'error').mockImplementation(() => {});

        await act(async () => accept().click());
        expect(mocks.error).toHaveBeenCalledWith({ key: 'feature:eula', error });
        expect(mocks.send).not.toHaveBeenCalled();
        expect(accept()).toBeDefined();
    });
});
