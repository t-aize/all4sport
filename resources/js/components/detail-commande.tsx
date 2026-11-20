import { ImageOff, MapPin, Package, Truck } from 'lucide-react';

import { formatDate, formatPrix } from '@/lib/format';
import type { CommandeDetail } from '@/types/catalogue';

/**
 * Détail d'une commande, commun à l'espace client et au back-office : lignes
 * (produit, quantité, prix unitaire, sous-total), totaux, date, lieu
 * d'expédition et adresse de livraison.
 *
 * @param commande La commande à présenter.
 */
export function DetailCommande({ commande }: { commande: CommandeDetail }) {
    return (
        <div className="grid gap-6 lg:grid-cols-[1fr_340px]">
            <section className="bg-card overflow-hidden rounded-3xl border">
                <h2 className="flex items-center gap-2 border-b p-5 font-semibold">
                    <Package className="text-primary size-5" /> Produits
                    commandés
                </h2>
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-muted-foreground text-xs uppercase">
                            <tr>
                                <th className="p-3 text-left font-medium">
                                    Produit
                                </th>
                                <th className="p-3 text-right font-medium">
                                    Prix unitaire
                                </th>
                                <th className="p-3 text-center font-medium">
                                    Quantité
                                </th>
                                <th className="p-3 text-right font-medium">
                                    Sous-total
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {commande.lignes.map((ligne) => (
                                <tr
                                    key={ligne.reference}
                                    className="hover:bg-muted/30 transition-colors"
                                >
                                    <td className="p-3">
                                        <div className="flex items-center gap-3">
                                            <span className="bg-muted flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg">
                                                {ligne.photo ? (
                                                    <img
                                                        src={ligne.photo}
                                                        alt=""
                                                        className="size-full object-cover"
                                                    />
                                                ) : (
                                                    <ImageOff className="text-muted-foreground size-4" />
                                                )}
                                            </span>
                                            <span>
                                                <span className="line-clamp-2 font-medium">
                                                    {ligne.description}
                                                </span>
                                                <span className="text-muted-foreground font-mono text-[11px]">
                                                    {ligne.reference}
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                    <td className="p-3 text-right whitespace-nowrap">
                                        {formatPrix(ligne.prixUnitaire)}
                                    </td>
                                    <td className="p-3 text-center">
                                        {ligne.quantite}
                                    </td>
                                    <td className="p-3 text-right font-semibold whitespace-nowrap">
                                        {formatPrix(ligne.sousTotal)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <dl className="flex flex-col gap-2 border-t p-5 text-sm">
                    <div className="flex justify-between">
                        <dt className="text-muted-foreground">Sous-total</dt>
                        <dd>{formatPrix(commande.sousTotal)}</dd>
                    </div>
                    <div className="flex justify-between">
                        <dt className="text-muted-foreground">
                            Frais de livraison
                        </dt>
                        <dd>
                            {Number(commande.fraisLivraison) === 0
                                ? 'Gratuit'
                                : formatPrix(commande.fraisLivraison)}
                        </dd>
                    </div>
                    <div className="flex items-baseline justify-between border-t pt-3">
                        <dt className="font-semibold">Total</dt>
                        <dd className="text-2xl font-black">
                            {formatPrix(commande.montantTotal)}
                        </dd>
                    </div>
                </dl>
            </section>

            <aside className="flex flex-col gap-4">
                <div className="bg-card rounded-3xl border p-5">
                    <h2 className="mb-3 flex items-center gap-2 font-semibold">
                        <Truck className="text-primary size-5" /> Livraison
                    </h2>
                    <dl className="flex flex-col gap-3 text-sm">
                        <div>
                            <dt className="text-muted-foreground text-xs">
                                Date de commande
                            </dt>
                            <dd className="font-medium">
                                {formatDate(commande.date)}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground text-xs">
                                Mode
                            </dt>
                            <dd className="font-medium">
                                {commande.modeLivraisonLibelle}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground text-xs">
                                Lieu d'expédition
                            </dt>
                            <dd className="font-medium">
                                {commande.lieuExpedition ?? '—'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground text-xs">
                                {commande.modeLivraison === 'retrait_magasin'
                                    ? 'Adresse du magasin de retrait'
                                    : 'Adresse de livraison'}
                            </dt>
                            <dd className="flex items-start gap-1.5 font-medium">
                                <MapPin className="text-primary mt-0.5 size-4 shrink-0" />
                                {commande.modeLivraison === 'retrait_magasin'
                                    ? commande.magasin
                                        ? `${commande.magasin.adresse}, ${commande.magasin.ville}`
                                        : '—'
                                    : (commande.adresseLivraison ?? '—')}
                            </dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    );
}
