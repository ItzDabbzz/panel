import { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { FilePickerError } from '@/lib/filePicker';
import useFilePicker from './useFilePicker';

const show = vi.fn();
const onFiles = vi.fn();
let container: HTMLDivElement;
let input: HTMLInputElement;
let root: Root;
let picker: ReturnType<typeof useFilePicker>;
let mounted: boolean;

function Harness() {
    picker = useFilePicker(onFiles, { multiple: false });
    return null;
}

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.stubGlobal('isSecureContext', true);
    vi.stubGlobal('showOpenFilePicker', show);
    show.mockReset();
    onFiles.mockReset();
    container = document.createElement('div');
    input = document.createElement('input');
    input.type = 'file';
    document.body.append(container, input);
    root = createRoot(container);
    mounted = true;
    await act(async () => root.render(<Harness />));
});

afterEach(async () => {
    if (mounted) await act(async () => root.unmount());
    container.remove();
    input.remove();
    vi.unstubAllGlobals();
});

function pending() {
    let resolve!: (handles: { getFile: () => Promise<File> }[]) => void;
    let reject!: (error: unknown) => void;
    show.mockReturnValueOnce(
        new Promise((success, failure) => {
            resolve = success;
            reject = failure;
        }),
    );
    return { resolve, reject };
}

it.each(['resolve', 'reject'] as const)(
    'ignores a selection that %ss after reset and permits another selection',
    async (outcome) => {
        const selection = pending();
        const file = new File(['SQL'], 'dump.sql');
        await act(async () => picker.open(input));
        expect(show).toHaveBeenCalledTimes(1);
        await act(async () => {
            picker.reset();
            picker.open(input);
        });
        expect(show).toHaveBeenCalledTimes(1);
        await act(async () => {
            if (outcome === 'resolve') selection.resolve([{ getFile: async () => file }]);
            else selection.reject(new Error('Old selection failed'));
        });
        expect(onFiles).not.toHaveBeenCalled();
        expect(picker.error).toBeNull();
        show.mockResolvedValueOnce([{ getFile: async () => file }]);
        await act(async () => picker.open(input));
        expect(onFiles).toHaveBeenCalledExactlyOnceWith([file]);
    },
);

it.each(['resolve', 'reject'] as const)('ignores a selection that %ss after unmount', async (outcome) => {
    const selection = pending();
    await act(async () => picker.open(input));
    await act(async () => root.unmount());
    mounted = false;
    await act(async () => {
        if (outcome === 'resolve') selection.resolve([{ getFile: async () => new File(['SQL'], 'old.sql') }]);
        else selection.reject(new Error('Old selection failed'));
    });
    expect(onFiles).not.toHaveBeenCalled();
    expect(picker.error).toBeNull();
});

it('suppresses duplicate openings and permits same-file reselection after completion', async () => {
    const selection = pending();
    const file = new File(['SQL'], 'dump.sql');
    await act(async () => {
        picker.open(input);
        picker.open(input);
        picker.openStandard(input);
    });
    expect(show).toHaveBeenCalledTimes(1);
    await act(async () => selection.resolve([{ getFile: async () => file }]));
    show.mockResolvedValueOnce([{ getFile: async () => file }]);
    await act(async () => picker.open(input));
    expect(onFiles.mock.calls).toEqual([[[file]], [[file]]]);
});

it('releases the pending state after cancellation or failure and only falls back on a new gesture', async () => {
    const legacy = vi.spyOn(input, 'click');
    show.mockRejectedValueOnce(new DOMException('Cancelled', 'AbortError'));
    await act(async () => picker.open(input));
    expect(picker.error).toBeNull();
    show.mockRejectedValueOnce(new Error('Picker failed'));
    await act(async () => picker.open(input));
    expect(picker.error).toBeInstanceOf(FilePickerError);
    expect(legacy).not.toHaveBeenCalled();
    await act(async () => picker.openStandard(input));
    expect(legacy).toHaveBeenCalledTimes(1);
    expect(picker.error).toBeNull();
});
