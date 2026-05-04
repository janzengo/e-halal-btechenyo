import { useEffect, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import { toast } from 'sonner';

interface FlashProps {
  flash?: {
    success?: string | null;
    error?: string | null;
  };
}

export function FlashToaster() {
  const { flash } = usePage<FlashProps>().props;
  const lastShown = useRef<string | null>(null);

  useEffect(() => {
    // Create a signature to avoid repeating same flash consecutively
    const signature = JSON.stringify({
      s: flash?.success ?? null,
      e: flash?.error ?? null,
    });

    if (signature === lastShown.current) return;

    if (flash?.success) {
      toast.success(flash.success);
    }
    if (flash?.error) {
      toast.error(flash.error);
    }

    lastShown.current = signature;
  }, [flash?.success, flash?.error]);

  return null;
}
