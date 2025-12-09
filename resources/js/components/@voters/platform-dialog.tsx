import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface Candidate {
    id: number;
    name: string;
    party: string;
    photo?: string;
    platform: string;
}

interface PlatformDialogProps {
    candidate: Candidate | null;
    isOpen: boolean;
    onClose: () => void;
}

export function PlatformDialog({ candidate, isOpen, onClose }: PlatformDialogProps) {
    if (!candidate) return null;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <div className="flex items-center gap-4">
                        <img
                            src={candidate.photo || '/images/profile.jpg'}
                            alt={candidate.name}
                            className="h-20 w-20 rounded-full object-cover"
                        />
                        <div>
                            <DialogTitle>{candidate.name}</DialogTitle>
                            <DialogDescription>{candidate.party}</DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <div className="py-4">
                    <h4 className="mb-2 font-semibold">Platform:</h4>
                    <p className="text-sm text-gray-700">{candidate.platform}</p>
                </div>
                <DialogFooter>
                    <Button onClick={onClose}>Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
