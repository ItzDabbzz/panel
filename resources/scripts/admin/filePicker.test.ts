import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import './filePicker';

const show = vi.fn();
const pond = { disabled: false, allowBrowse: true, addFile: vi.fn() };
let field: HTMLDivElement;
let input: HTMLInputElement;

beforeEach(() => {
    vi.stubGlobal('isSecureContext', true);
    vi.stubGlobal('showOpenFilePicker', show);
    vi.stubGlobal('FilePond', { find: vi.fn(() => pond) });
    show.mockReset();
    pond.addFile.mockReset().mockResolvedValue({});
    pond.disabled = false;
    pond.allowBrowse = true;
    field = document.createElement('div');
    field.dataset.compatibleFilePicker = 'json';
    field.dataset.filePickerSelectError = 'Selection failed';
    field.dataset.filePickerReadError = 'Read failed';
    field.dataset.filePickerFallback = 'Standard picker';
    field.innerHTML = '<div class="filepond--root"><input type="file"></div>';
    input = field.querySelector('input')!;
    document.body.append(field);
});
afterEach(async () => {
    await vi.waitFor(() => {});
    field.remove();
    vi.unstubAllGlobals();
});
const click = () => input.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

describe('Filament picker bridge', () => {
    it('suppresses the native chooser and passes a single File into FilePond', async () => {
        const file = new File(['{}'], 'egg.json', { type: 'application/json' });
        show.mockResolvedValue([{ getFile: () => Promise.resolve(file) }]);
        expect(click()).toBe(false);
        expect(show).toHaveBeenCalledExactlyOnceWith({
            multiple: false,
            types: [{ accept: { 'application/json': ['.json'] } }],
        });
        await vi.waitFor(() => expect(pond.addFile).toHaveBeenCalledExactlyOnceWith(file));
    });

    it('keeps extension selection unrestricted and delegates invalid-file validation to FilePond', async () => {
        field.dataset.compatibleFilePicker = 'any';
        const file = new File(['invalid'], 'invalid.txt');
        show.mockResolvedValue([{ getFile: () => Promise.resolve(file) }]);
        pond.addFile.mockRejectedValue({ error: 'Invalid file' });
        click();
        expect(show).toHaveBeenCalledExactlyOnceWith({ multiple: false });
        await vi.waitFor(() => expect(pond.addFile).toHaveBeenCalledWith(file));
        expect(field.querySelector('[role="alert"]')).toBeNull();
    });

    it('leaves unsupported, unmarked and disabled controls on their existing path', () => {
        vi.stubGlobal('showOpenFilePicker', undefined);
        expect(click()).toBe(true);
        vi.stubGlobal('showOpenFilePicker', show);
        delete field.dataset.compatibleFilePicker;
        expect(click()).toBe(true);
        field.dataset.compatibleFilePicker = 'json';
        pond.disabled = true;
        expect(click()).toBe(true);
        expect(show).not.toHaveBeenCalled();
    });

    it('cancels quietly and suppresses repeated clicks while a picker is pending', async () => {
        let reject!: (error: unknown) => void;
        show.mockReturnValue(
            new Promise((_, failure) => {
                reject = failure;
            }),
        );
        click();
        expect(click()).toBe(false);
        expect(show).toHaveBeenCalledTimes(1);
        reject(new DOMException('Cancelled', 'AbortError'));
        await new Promise((resolve) => setTimeout(resolve, 0));
        expect(pond.addFile).not.toHaveBeenCalled();
        expect(field.querySelector('[role="alert"]')).toBeNull();
    });

    it('provides a fresh-gesture fallback after a read failure without reopening automatically', async () => {
        show.mockResolvedValue([{ getFile: () => Promise.reject(new Error('Read failed')) }]);
        click();
        await vi.waitFor(() => expect(field.querySelector('[role="alert"]')?.textContent).toContain('Read failed'));
        expect(pond.addFile).not.toHaveBeenCalled();
        const legacy = vi.spyOn(input, 'click');
        field.querySelector<HTMLButtonElement>('button')!.click();
        expect(legacy).toHaveBeenCalledTimes(1);
        expect(show).toHaveBeenCalledTimes(1);
    });
});
