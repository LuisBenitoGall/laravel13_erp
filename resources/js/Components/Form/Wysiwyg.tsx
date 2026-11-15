import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { Bold, Heading2, Heading3, Italic, List, ListOrdered } from 'lucide-react';
import { useEffect } from 'react';
import { useTranslation } from 'react-i18next';

import { cn } from '@/lib/utils';

export type WysiwygProps = {
    id?: string;
    value?: string | null;
    onChange: (html: string) => void;
    disabled?: boolean;
    className?: string;
    'aria-invalid'?: boolean;
};

export function Wysiwyg({
    id,
    value = '',
    onChange,
    disabled = false,
    className,
    'aria-invalid': ariaInvalid,
}: WysiwygProps) {
    const { t } = useTranslation();
    const html = value ?? '';

    const editor = useEditor({
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
            }),
        ],
        content: html,
        editable: !disabled,
        immediatelyRender: false,
        onUpdate: ({ editor: current }) => {
            onChange(current.getHTML());
        },
        editorProps: {
            attributes: {
                id: id ?? '',
                class: cn(
                    'min-h-32 px-3 py-2 text-sm leading-relaxed outline-none',
                    '[&_h2]:mb-2 [&_h2]:mt-3 [&_h2]:text-base [&_h2]:font-semibold',
                    '[&_h3]:mb-1 [&_h3]:mt-2 [&_h3]:text-sm [&_h3]:font-semibold',
                    '[&_ul]:my-2 [&_ul]:list-disc [&_ul]:pl-5',
                    '[&_ol]:my-2 [&_ol]:list-decimal [&_ol]:pl-5',
                    '[&_p]:my-1',
                ),
                'aria-invalid': ariaInvalid ? 'true' : 'false',
            },
        },
    });

    useEffect(() => {
        if (!editor) {
            return;
        }

        editor.setEditable(!disabled);
    }, [disabled, editor]);

    useEffect(() => {
        if (!editor) {
            return;
        }

        const current = editor.getHTML();
        if (html !== current) {
            editor.commands.setContent(html, { emitUpdate: false });
        }
    }, [html, editor]);

    if (!editor) {
        return (
            <div
                className={cn(
                    'min-h-40 rounded-md border border-slate-300 bg-white',
                    className,
                )}
            />
        );
    }

    const btn = (active: boolean) =>
        cn(
            'inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-600',
            'hover:bg-slate-100 hover:text-slate-900',
            'disabled:cursor-not-allowed disabled:opacity-50',
            active && 'bg-teal-50 text-teal-800',
        );

    return (
        <div
            className={cn(
                'overflow-hidden rounded-md border border-slate-300 bg-white shadow-xs',
                'focus-within:border-teal-700 focus-within:ring-2 focus-within:ring-teal-700/30',
                ariaInvalid && 'border-red-500',
                disabled && 'opacity-50',
                className,
            )}
        >
            <div
                className="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50 p-1"
                role="toolbar"
                aria-label={t('editor_formato')}
            >
                <button
                    type="button"
                    className={btn(editor.isActive('bold'))}
                    disabled={disabled}
                    aria-label={t('negrita')}
                    aria-pressed={editor.isActive('bold')}
                    onClick={() => editor.chain().focus().toggleBold().run()}
                >
                    <Bold className="h-4 w-4" />
                </button>
                <button
                    type="button"
                    className={btn(editor.isActive('italic'))}
                    disabled={disabled}
                    aria-label={t('cursiva')}
                    aria-pressed={editor.isActive('italic')}
                    onClick={() => editor.chain().focus().toggleItalic().run()}
                >
                    <Italic className="h-4 w-4" />
                </button>
                <button
                    type="button"
                    className={btn(editor.isActive('heading', { level: 2 }))}
                    disabled={disabled}
                    aria-label={t('titulo_2')}
                    aria-pressed={editor.isActive('heading', { level: 2 })}
                    onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}
                >
                    <Heading2 className="h-4 w-4" />
                </button>
                <button
                    type="button"
                    className={btn(editor.isActive('heading', { level: 3 }))}
                    disabled={disabled}
                    aria-label={t('titulo_3')}
                    aria-pressed={editor.isActive('heading', { level: 3 })}
                    onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()}
                >
                    <Heading3 className="h-4 w-4" />
                </button>
                <button
                    type="button"
                    className={btn(editor.isActive('bulletList'))}
                    disabled={disabled}
                    aria-label={t('lista_vinetas')}
                    aria-pressed={editor.isActive('bulletList')}
                    onClick={() => editor.chain().focus().toggleBulletList().run()}
                >
                    <List className="h-4 w-4" />
                </button>
                <button
                    type="button"
                    className={btn(editor.isActive('orderedList'))}
                    disabled={disabled}
                    aria-label={t('lista_numerada')}
                    aria-pressed={editor.isActive('orderedList')}
                    onClick={() => editor.chain().focus().toggleOrderedList().run()}
                >
                    <ListOrdered className="h-4 w-4" />
                </button>
            </div>
            <EditorContent editor={editor} />
        </div>
    );
}
