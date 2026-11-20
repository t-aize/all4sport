import { iconeEtat } from '@/components/etat-badge';
import { formatDateHeure } from '@/lib/format';
import type { EtapeSuivi } from '@/types/catalogue';

/**
 * Frise de suivi d'une commande : chaque état du parcours (transmise ->
 * validée -> en préparation -> expédiée -> livrée/retirée), avec sa date s'il est
 * atteint. L'étape en cours est mise en avant.
 *
 * @param etapes Les étapes du parcours, dans l'ordre.
 */
export function SuiviCommande({ etapes }: { etapes: EtapeSuivi[] }) {
    const derniereAtteinte = etapes.reduce(
        (indice, etape, position) => (etape.date ? position : indice),
        -1,
    );

    return (
        <ol className="grid gap-4 sm:grid-cols-5 sm:gap-2">
            {etapes.map((etape, position) => {
                const atteinte = etape.date !== null;
                const courante = position === derniereAtteinte;
                const Icone = iconeEtat(etape.etat);

                return (
                    <li
                        key={etape.etat}
                        className="relative flex items-center gap-3 sm:flex-col sm:text-center"
                    >
                        {position < etapes.length - 1 && (
                            <span
                                aria-hidden
                                className={`absolute top-5 left-[calc(50%+1.5rem)] hidden h-0.5 w-[calc(100%-3rem)] rounded-full sm:block ${
                                    position < derniereAtteinte
                                        ? 'bg-primary'
                                        : 'bg-border'
                                }`}
                            />
                        )}
                        <span
                            className={`relative z-10 flex size-10 shrink-0 items-center justify-center rounded-full border-2 transition-colors ${
                                atteinte
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border bg-background text-muted-foreground'
                            } ${courante ? 'ring-primary/25 ring-4' : ''}`}
                        >
                            <Icone className="size-4" aria-hidden />
                        </span>
                        <span className="flex flex-col">
                            <span
                                className={`text-sm font-medium ${atteinte ? '' : 'text-muted-foreground'}`}
                            >
                                {etape.libelle}
                            </span>
                            <span className="text-muted-foreground text-xs">
                                {atteinte
                                    ? formatDateHeure(etape.date)
                                    : 'À venir'}
                            </span>
                        </span>
                    </li>
                );
            })}
        </ol>
    );
}
