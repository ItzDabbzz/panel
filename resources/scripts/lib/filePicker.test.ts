import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { FilePickerError, pickFiles } from './filePicker';

const show = vi.fn();
beforeEach(() => {
    vi.stubGlobal('isSecureContext', true);
    vi.stubGlobal('showOpenFilePicker', show);
    show.mockReset();
});
afterEach(() => vi.unstubAllGlobals());

describe('file picker selection', () => {
    it('opens synchronously with the requested options and reads every handle', async () => {
        const files = [new File(['a'], 'a.zip'), new File(['b'], 'b.sql')];
        show.mockResolvedValue(files.map((file) => ({ getFile: () => Promise.resolve(file) })));
        const options = { multiple: true };
        const selection = pickFiles(options);
        expect(show).toHaveBeenCalledExactlyOnceWith(options);
        expect(await selection).toEqual(files);
        expect(await pickFiles(options)).toEqual(files); // Same-file reselection has no cached input value.
    });

    it('returns synchronous fallback when unsupported or insecure', () => {
        vi.stubGlobal('isSecureContext', false);
        expect(pickFiles()).toBeNull();
        expect(show).not.toHaveBeenCalled();
        vi.stubGlobal('isSecureContext', true);
        vi.stubGlobal('showOpenFilePicker', undefined);
        expect(pickFiles()).toBeNull();
    });

    it('treats only picker AbortError as quiet cancellation', async () => {
        show.mockRejectedValue(new DOMException('Cancelled', 'AbortError'));
        expect(await pickFiles({ multiple: false })).toBeNull();
        expect(show).toHaveBeenCalledTimes(1);
    });

    it('reports synchronous and asynchronous picker failures without opening another picker', async () => {
        const error = new DOMException('No activation', 'SecurityError');
        show.mockImplementationOnce(() => {
            throw error;
        }).mockRejectedValueOnce(error);
        for (let i = 0; i < 2; i++) {
            await expect(pickFiles()).rejects.toMatchObject({ stage: 'select', reason: error });
        }
        expect(show).toHaveBeenCalledTimes(2);
    });

    it('reports file-read failures, including AbortError, and never supplies a partial selection', async () => {
        const error = new DOMException('Read failed', 'AbortError');
        show.mockResolvedValue([
            { getFile: () => Promise.resolve(new File(['ok'], 'ok.zip')) },
            { getFile: () => Promise.reject(error) },
        ]);
        await expect(pickFiles()).rejects.toBeInstanceOf(FilePickerError);
        await expect(pickFiles()).rejects.toMatchObject({ stage: 'read', reason: error });
    });
});
