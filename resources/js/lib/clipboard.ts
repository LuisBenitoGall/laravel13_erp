/**
 * Copia texto al portapapeles. Usa Clipboard API y, si falla (p. ej. HTTP
 * en dominio .test / sin secure context), fallback con textarea + execCommand.
 */
export async function copyText(value: string): Promise<boolean> {
    if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(value);

            return true;
        } catch {
            // Secure context / permission — try legacy fallback.
        }
    }

    try {
        const textarea = document.createElement('textarea');
        textarea.value = value;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.top = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        textarea.setSelectionRange(0, value.length);
        const ok = document.execCommand('copy');
        document.body.removeChild(textarea);

        return ok;
    } catch {
        return false;
    }
}
