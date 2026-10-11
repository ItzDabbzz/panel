export interface FilePickerOptions {
    multiple?: boolean;
    types?: { accept: Record<string, string[]> }[];
}

interface PickerWindow extends Window {
    showOpenFilePicker?: (options: FilePickerOptions) => Promise<{ getFile: () => Promise<File> }[]>;
}

export class FilePickerError extends Error {
    constructor(
        public readonly stage: 'select' | 'read',
        public readonly reason: unknown,
    ) {
        super(stage === 'select' ? 'File selection failed.' : 'Reading the selected file failed.');
    }
}

export const pickFiles = (options: FilePickerOptions = {}): Promise<File[] | null> | null => {
    const pickerWindow = window as PickerWindow;
    if (!window.isSecureContext || typeof pickerWindow.showOpenFilePicker !== 'function') return null;

    let selection: Promise<{ getFile: () => Promise<File> }[]>;
    try {
        selection = pickerWindow.showOpenFilePicker(options);
    } catch (error) {
        selection = Promise.reject(error);
    }

    return selection.then(
        async (handles) => {
            try {
                return await Promise.all(handles.map((handle) => handle.getFile()));
            } catch (error) {
                throw new FilePickerError('read', error);
            }
        },
        (error: unknown) => {
            if (error && typeof error === 'object' && 'name' in error && error.name === 'AbortError') return null;
            throw new FilePickerError('select', error);
        },
    );
};
