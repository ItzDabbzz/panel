import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import JavaVersionModalFeature from './JavaVersionModalFeature';

const mocks = vi.hoisted(() => ({
    data: undefined as { dockerImages: Record<string, string> } | undefined,
    isValidating: true,
    mutate: vi.fn(),
    update: vi.fn(),
    consoleOutput: (_message: string) => {},
}));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'server-uuid' } }, status: { value: 'offline' }, socket: {} }),
    },
}));
vi.mock('@/api/swr/getServerStartup', () => ({
    default: () => ({ data: mocks.data, isValidating: mocks.isValidating, mutate: mocks.mutate }),
}));
vi.mock('@/api/server/setSelectedDockerImage', () => ({ default: mocks.update }));
vi.mock('@/plugins/useWebsocketEvent', () => ({
    default: (_event: string, callback: (message: string) => void) => {
        mocks.consoleOutput = callback;
    },
}));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({ visible, children }: { visible: boolean; children: ReactNode }) => (visible ? children : null),
}));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/InputSpinner', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));

let container: HTMLDivElement;
let root: Root;
let finishRefresh: () => void;
const images = { 'Java 8': 'image:8', 'Java 21': 'image:21' };

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.data = { dockerImages: images };
    mocks.isValidating = true;
    mocks.update.mockReset().mockResolvedValue(undefined);
    mocks.mutate.mockReset().mockImplementation(
        () =>
            new Promise((resolve) => {
                finishRefresh = () => {
                    mocks.isValidating = false;
                    resolve(mocks.data);
                };
            }),
    );
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
});

const render = async () => {
    await act(async () => root.render(<JavaVersionModalFeature />));
};
const open = async () => {
    await render();
    await act(async () => mocks.consoleOutput('java.lang.UnsupportedClassVersionError'));
};
const selectImage = async (image: string) => {
    const select = container.querySelector('select')!;
    await act(async () => {
        select.value = image;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });
};
const refresh = async () => {
    await act(async () => finishRefresh());
    await render();
};
const updateButton = () =>
    Array.from(container.querySelectorAll('button')).find((button) => button.textContent === 'Update Docker Image')!;

describe('recovery image selection', () => {
    it('displays and submits the user selection after a pending refresh completes', async () => {
        await open();
        expect(mocks.mutate).toHaveBeenCalledOnce();
        await selectImage('image:21');
        await refresh();

        expect(container.querySelector('select')!.value).toBe('image:21');
        await act(async () => updateButton().click());
        expect(mocks.update).toHaveBeenCalledWith('server-uuid', 'image:21');
    });

    it('submits the displayed default before refresh completes', async () => {
        await open();
        expect(container.querySelector('select')!.value).toBe('image:8');
        await act(async () => updateButton().click());
        expect(mocks.update).toHaveBeenCalledWith('server-uuid', 'image:8');
    });

    it('displays and submits the same fallback when the selected image is removed', async () => {
        await open();
        await selectImage('image:21');
        mocks.data = { dockerImages: { 'Java 17': 'image:17' } };
        await refresh();

        expect(container.querySelector('select')!.value).toBe('image:17');
        await act(async () => updateButton().click());
        expect(mocks.update).toHaveBeenCalledWith('server-uuid', 'image:17');
    });

    it.each([undefined, { dockerImages: {} }])('prevents submission without available images (%j)', async (data) => {
        mocks.data = data;
        await open();
        expect(container.querySelector('select')!.disabled).toBe(true);
        expect(updateButton().disabled).toBe(true);
        await act(async () => updateButton().click());
        expect(mocks.update).not.toHaveBeenCalled();
    });
});
