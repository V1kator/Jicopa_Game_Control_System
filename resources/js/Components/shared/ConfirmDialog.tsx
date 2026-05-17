import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

interface ConfirmDialogProps {
    open: boolean;
    title: string;
    message: string;
    onConfirm: () => void;
    onCancel: () => void;
    variant?: 'danger' | 'default';
    confirmText?: string;
}

export default function ConfirmDialog({
    open,
    title,
    message,
    onConfirm,
    onCancel,
    variant = 'danger',
    confirmText,
}: ConfirmDialogProps) {
    const defaultText = variant === 'danger' ? 'Desativar' : 'Reativar';
    
    return (
        <Dialog open={open} onOpenChange={(v) => { if (!v) onCancel(); }}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    <DialogDescription>{message}</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <button
                        onClick={onCancel}
                        className="border border-gray-300 px-4 py-2 rounded-md text-sm hover:bg-gray-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={onConfirm}
                        className={
                            variant === 'danger'
                                ? 'bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700'
                                : 'bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700'
                        }
                    >
                        {confirmText || defaultText}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
