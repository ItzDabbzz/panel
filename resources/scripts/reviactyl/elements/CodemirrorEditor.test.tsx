import { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { EditorView } from '@codemirror/view';
import { undo, undoDepth } from '@codemirror/commands';
import { language } from '@codemirror/language';
import CodemirrorEditor from './CodemirrorEditor';

let container: HTMLDivElement;
let root: Root;
const fetchContent = vi.fn();
const onModeChanged = vi.fn();

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

const render = async (initialContent = 'loaded file', onContentSaved = vi.fn(), mode = 'text/plain') => {
    await act(async () => {
        root.render(
            <CodemirrorEditor
                initialContent={initialContent}
                mode={mode}
                fetchContent={fetchContent}
                onModeChanged={onModeChanged}
                onContentSaved={onContentSaved}
            />,
        );
    });
    return EditorView.findFromDOM(container.querySelector<HTMLElement>('.cm-editor')!)!;
};

it('reads edits and resets undo history when another file is loaded', async () => {
    const view = await render();
    view.dispatch({ changes: { from: view.state.doc.length, insert: ' edited' } });
    await expect(fetchContent.mock.calls.at(-1)![0]()).resolves.toBe('loaded file edited');
    expect(undoDepth(view.state)).toBe(1);
    expect(undo(view)).toBe(true);
    expect(view.state.doc.toString()).toBe('loaded file');

    const replacement = await render('another file');
    expect(container.querySelectorAll('.cm-editor')).toHaveLength(1);
    await expect(fetchContent.mock.calls.at(-1)![0]()).resolves.toBe('another file');
    expect(undoDepth(replacement.state)).toBe(0);
});

it('keeps edits when the save callback changes and uses the latest callback for Ctrl-S', async () => {
    const originalSave = vi.fn();
    const currentSave = vi.fn();
    const view = await render('loaded file', originalSave);
    view.dispatch({ changes: { from: view.state.doc.length, insert: ' edited' } });
    expect(await render('loaded file', currentSave)).toBe(view);
    const event = new KeyboardEvent('keydown', { key: 's', ctrlKey: true, bubbles: true, cancelable: true });
    view.contentDOM.dispatchEvent(event);
    expect(currentSave).toHaveBeenCalledOnce();
    expect(originalSave).not.toHaveBeenCalled();
    expect(event.defaultPrevented).toBe(true);
    expect(view.state.doc.toString()).toBe('loaded file edited');
});

it('loads syntax support and removes it when the mode switches to plain text', async () => {
    const view = await render('const x = 1;', vi.fn(), 'text/javascript');
    await vi.waitFor(() => expect(view.state.facet(language)).not.toBeNull());
    await render('const x = 1;');
    expect(view.state.facet(language)).toBeNull();
});
