import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

export default function ConfirmDialog({
    open,
    onOpenChange,
    title,
    description,
    confirmLabel,
    cancelLabel,
    onConfirm,
    destructive = true,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    onConfirm: () => void;
    destructive?: boolean;
}) {
    const { t } = useTranslation();

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description ? (
                        <DialogDescription>{description}</DialogDescription>
                    ) : null}
                </DialogHeader>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={() => onOpenChange(false)}
                    >
                        {cancelLabel ?? t('cancelar')}
                    </Button>
                    <Button
                        type="button"
                        variant={destructive ? 'default' : 'default'}
                        className={
                            destructive
                                ? 'bg-red-700 text-white hover:bg-red-800'
                                : undefined
                        }
                        onClick={() => {
                            onOpenChange(false);
                            onConfirm();
                        }}
                    >
                        {confirmLabel ?? t('confirmar')}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
