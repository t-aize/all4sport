import {
    CheckCheck,
    CircleCheck,
    PackageOpen,
    Send,
    Store,
    Truck,
} from 'lucide-react';

import type { EtatCommande } from '@/types/catalogue';

/**
 * Couleur et icône associées à chaque état de commande.
 */
const STYLES: Record<EtatCommande, { classes: string; icone: typeof Send }> = {
    transmise: {
        classes: 'bg-sky-500/10 text-sky-700 ring-sky-500/20 dark:text-sky-300',
        icone: Send,
    },
    validee: {
        classes:
            'bg-indigo-500/10 text-indigo-700 ring-indigo-500/20 dark:text-indigo-300',
        icone: CircleCheck,
    },
    en_preparation: {
        classes:
            'bg-amber-500/10 text-amber-700 ring-amber-500/25 dark:text-amber-300',
        icone: PackageOpen,
    },
    expediee: {
        classes:
            'bg-orange-500/10 text-orange-700 ring-orange-500/25 dark:text-orange-300',
        icone: Truck,
    },
    livree: {
        classes:
            'bg-emerald-500/10 text-emerald-700 ring-emerald-500/25 dark:text-emerald-300',
        icone: CheckCheck,
    },
    retiree: {
        classes:
            'bg-emerald-500/10 text-emerald-700 ring-emerald-500/25 dark:text-emerald-300',
        icone: Store,
    },
};

/**
 * Pastille colorée indiquant l'état d'une commande.
 *
 * @param etat Valeur technique de l'état.
 * @param libelle Libellé affiché (« En préparation »…).
 */
export function EtatBadge({
    etat,
    libelle,
}: {
    etat: EtatCommande;
    libelle: string;
}) {
    const { classes, icone: Icone } = STYLES[etat];

    return (
        <span
            className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset ${classes}`}
        >
            <Icone className="size-3.5" aria-hidden />
            {libelle}
        </span>
    );
}

/**
 * L'icône d'un état, réutilisée par la frise de suivi.
 */
export function iconeEtat(etat: EtatCommande) {
    return STYLES[etat].icone;
}
