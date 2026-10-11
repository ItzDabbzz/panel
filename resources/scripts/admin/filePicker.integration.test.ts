import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { afterEach, expect, it, vi } from 'vitest';
import './filePicker';

interface RealPond {
    element: HTMLElement;
    addFile: (file: File) => Promise<unknown>;
    getFiles: () => { file: File; serverId: string | null; status: number }[];
    destroy: () => void;
}
interface UploadComponent {
    pond: RealPond;
    init: () => Promise<void>;
    destroy: () => void;
    [key: string]: unknown;
}
interface FilamentWindow extends Window {
    FilePond: { find: (element: Element) => RealPond | null };
}

let component: UploadComponent | undefined;
let field: HTMLElement | undefined;

afterEach(() => {
    component?.destroy();
    component = undefined;
    field?.remove();
    vi.unstubAllGlobals();
});

it.each(['json', 'any'])(
    'connects the real %s action field to Filament’s initialized FilePond upload lifecycle',
    async (kind) => {
        const html = execFileSync('php', ['tests/Fixtures/file-picker-field.php', kind], {
            env: {
                ...process.env,
                APP_ENV: 'testing',
                APP_KEY: 'SomeRandomString3232RandomString',
                CACHE_STORE: 'array',
                SESSION_DRIVER: 'array',
                REDIS_CLIENT: 'predis',
            },
            encoding: 'utf8',
        });
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        field = wrapper.querySelector<HTMLElement>('[data-compatible-file-picker]')!;
        document.body.append(field);
        expect(field.dataset.compatibleFilePicker).toBe(kind);
        const original = field.querySelector<HTMLInputElement>('input[type="file"]')!;

        // Execute the installed Filament bundle, including its actual FilePond exports.
        const source = readFileSync('vendor/filament/forms/dist/components/file-upload.js', 'utf8');
        const factory = new Function(source.replace(/export\{(\w+) as default\}/, 'return $1'))() as (
            options: Record<string, unknown>,
        ) => UploadComponent;
        const upload = vi.fn((_path: string, _file: File, finish: () => void) => finish());
        const wire = { $entangle: () => ({}), upload, cancelUpload: vi.fn() };
        // Evaluate the actual rendered Alpine configuration; only the server transport is stubbed.
        component = new Function(
            'fileUploadFormComponent',
            '$wire',
            'Livewire',
            `return ${field.getAttribute('x-data')}`,
        )(factory, wire, { fireAction: async () => ({}) }) as UploadComponent;
        Object.assign(component, {
            $el: field,
            $refs: { input: original },
            $watch: vi.fn(),
        });
        // Happy DOM has no layout engine; tell Filament the rendered field is visible.
        Object.defineProperty(field, 'offsetParent', { value: document.body });
        // FilePond enumerates own NamedNodeMap keys; Happy DOM also exposes internal keys.
        Object.defineProperty(original, 'attributes', { value: Array.from(original.attributes) });
        await component.init();
        const pond = component.pond;
        expect(pond).toBeTruthy();
        expect((window as unknown as FilamentWindow).FilePond.find(pond.element)?.element).toBe(pond.element);
        await vi.waitFor(() => expect(pond.element.querySelector('input[type="file"]')).not.toBeNull());
        const input = pond.element.querySelector<HTMLInputElement>('input[type="file"]')!;
        expect(input.closest('[data-compatible-file-picker]')).toBe(field);
        const file = new File([kind === 'json' ? '{}' : 'fixture'], kind === 'json' ? 'egg.json' : 'fixture.rext', {
            type: kind === 'json' ? 'application/json' : 'application/octet-stream',
        });
        const show = vi.fn().mockResolvedValue([{ getFile: async () => file }]);
        vi.stubGlobal('isSecureContext', true);
        vi.stubGlobal('showOpenFilePicker', show);
        expect(input.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }))).toBe(false);
        await vi.waitFor(() => expect(upload).toHaveBeenCalled(), { timeout: 5000 });
        expect(upload.mock.calls[0]?.[1]).toBe(file);
        await vi.waitFor(() => expect(pond.getFiles()[0]?.status).toBe(5), { timeout: 3000 });
        expect(pond.getFiles()[0]?.serverId).toBeTruthy();
        expect(upload.mock.calls[0]?.[0]).toBe(`data.file.${pond.getFiles()[0]?.serverId}`);
        expect(show).toHaveBeenCalledTimes(1);
    },
    10000,
);
