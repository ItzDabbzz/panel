import { useEffect, useRef, useState } from 'react';
import { FilePickerError, FilePickerOptions, pickFiles } from '@/lib/filePicker';

export default (onFiles: (files: File[]) => void, options: FilePickerOptions = {}) => {
    const busy = useRef(false);
    const generation = useRef(0);
    const [error, setError] = useState<FilePickerError | null>(null);

    useEffect(
        () => () => {
            generation.current++;
        },
        [],
    );

    const reset = () => {
        generation.current++;
        setError(null);
    };

    const open = (input: HTMLInputElement | null) => {
        if (!input || input.disabled || busy.current) return;
        setError(null);
        const selection = pickFiles(options);
        if (!selection) {
            input.click();
            return;
        }

        busy.current = true;
        const current = generation.current;
        void selection
            .then((files) => {
                if (current === generation.current && files?.length) onFiles(files);
            })
            .catch((reason: unknown) => {
                if (current === generation.current) {
                    setError(reason instanceof FilePickerError ? reason : new FilePickerError('read', reason));
                }
            })
            .finally(() => {
                busy.current = false;
            });
    };

    const openStandard = (input: HTMLInputElement | null) => {
        if (!input || input.disabled || busy.current) return;
        setError(null);
        input.click();
    };

    return { open, openStandard, error, reset };
};
