import { usePage } from '@inertiajs/react';
import { CircleAlert, CircleCheck, X } from 'lucide-react';
import { useEffect, useState } from 'react';

/**
 * Message de confirmation (« Produit ajouté au panier. ») envoyé par Laravel
 * avec `->with('status', ...)`, affiché en bas à droite puis masqué après
 * quelques secondes. Les erreurs générales (stock, panier) sont affichées de
 * la même façon, en rouge.
 */
export function MessageFlash() {
    const { status, errors } = usePage<{
        status?: string | null;
        errors: Record<string, string>;
    }>().props;
    const erreur =
        errors.panier ?? errors.stock ?? errors.fournisseur ?? errors.etat;
    const message = erreur ?? status ?? null;
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (!message) {
            return;
        }

        setVisible(true);
        const minuteur = window.setTimeout(() => setVisible(false), 5000);

        return () => window.clearTimeout(minuteur);
    }, [message]);

    if (!message || !visible) {
        return null;
    }

    const Icone = erreur ? CircleAlert : CircleCheck;

    return (
        <div
            role="status"
            aria-live="polite"
            className="animate-in fade-in slide-in-from-bottom-4 fixed right-4 bottom-4 z-50 w-[calc(100%-2rem)] max-w-sm duration-300"
        >
            <div
                className={`bg-card flex items-start gap-3 rounded-xl border p-4 shadow-2xl ${
                    erreur ? 'border-destructive/40' : 'border-primary/30'
                }`}
            >
                <Icone
                    className={`mt-0.5 size-5 shrink-0 ${erreur ? 'text-destructive' : 'text-emerald-600'}`}
                    aria-hidden
                />
                <p className="flex-1 text-sm">{message}</p>
                <button
                    type="button"
                    onClick={() => setVisible(false)}
                    className="text-muted-foreground hover:text-foreground"
                    aria-label="Fermer"
                >
                    <X className="size-4" />
                </button>
            </div>
        </div>
    );
}
