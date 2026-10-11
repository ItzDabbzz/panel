import { FilePickerError, pickFiles } from '@/lib/filePicker';

interface Pond {
    disabled: boolean;
    allowBrowse: boolean;
    addFile: (file: File) => Promise<unknown>;
}

interface PondWindow extends Window {
    FilePond?: { find: (element: Element) => Pond | null };
}

const busy = new WeakSet<HTMLElement>();
const standard = new WeakSet<HTMLInputElement>();

document.addEventListener(
    'click',
    (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || input.disabled || standard.has(input)) {
            return;
        }
        const field = input.closest<HTMLElement>('[data-compatible-file-picker]');
        const root = input.closest('.filepond--root');
        if (!field || !root) return;
        const pond = (window as PondWindow).FilePond?.find(root);
        if (!pond || pond.disabled || !pond.allowBrowse) return;
        if (busy.has(field)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        const selection = pickFiles({
            multiple: false,
            ...(field.dataset.compatibleFilePicker === 'json'
                ? { types: [{ accept: { 'application/json': ['.json'] } }] }
                : {}),
        });
        if (!selection) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        busy.add(field);
        field.querySelector('[data-file-picker-error]')?.remove();

        void selection
            .then(async (files) => {
                if (!files?.[0] || !field.isConnected || input.disabled || pond.disabled) return;
                // addFile retains type/size validation and Filament's Livewire server.process.
                // FilePond displays its own validation/upload errors.
                await pond.addFile(files[0]).catch(() => {});
            })
            .catch((error: unknown) => {
                if (!field.isConnected) return;
                const alert = document.createElement('div');
                alert.dataset.filePickerError = '';
                alert.setAttribute('role', 'alert');
                const message = document.createElement('p');
                message.textContent =
                    error instanceof FilePickerError && error.stage === 'read'
                        ? (field.dataset.filePickerReadError ?? '')
                        : (field.dataset.filePickerSelectError ?? '');
                const retry = document.createElement('button');
                retry.type = 'button';
                retry.className = 'fi-link';
                retry.textContent = field.dataset.filePickerFallback ?? '';
                retry.addEventListener('click', () => {
                    if (busy.has(field) || input.disabled || pond.disabled || !pond.allowBrowse) return;
                    standard.add(input);
                    try {
                        input.click();
                    } finally {
                        standard.delete(input);
                    }
                });
                alert.append(message, retry);
                field.append(alert);
            })
            .finally(() => busy.delete(field));
    },
    true,
);
