/**
 * Fonctions de mise en forme partagées par les pages (prix, dates).
 */

const formateurPrix = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

const formateurDate = new Intl.DateTimeFormat('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const formateurDateHeure = new Intl.DateTimeFormat('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

/**
 * Formate un montant en euros : "1 234,50 €".
 *
 * @param montant Montant envoyé par Laravel (chaîne "12.50" ou nombre).
 */
export function formatPrix(montant: string | number): string {
    return formateurPrix.format(Number(montant));
}

/**
 * Formate une date ISO ("2026-11-20") en "20 novembre 2026".
 */
export function formatDate(date: string | null | undefined): string {
    if (!date) {
        return '-';
    }

    return formateurDate.format(new Date(date));
}

/**
 * Formate une date et heure ISO en "20 nov. 2026, 14:05".
 */
export function formatDateHeure(date: string | null | undefined): string {
    if (!date) {
        return '-';
    }

    return formateurDateHeure.format(new Date(date));
}

/**
 * Accorde un mot selon un nombre : pluriel(3, 'article') -> "3 articles".
 */
export function pluriel(
    nombre: number,
    mot: string,
    motPluriel?: string,
): string {
    return `${nombre} ${nombre > 1 ? (motPluriel ?? `${mot}s`) : mot}`;
}
