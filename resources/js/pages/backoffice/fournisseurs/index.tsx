import { Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { type FormEvent, useState } from 'react';

import { destroy } from '@/actions/App/Http/Controllers/BackOffice/FournisseurController';
import { Pagination } from '@/components/pagination';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import BackOfficeLayout from '@/layouts/back-office-layout';
import { formatDate } from '@/lib/format';
import { create, edit, index } from '@/routes/backoffice/fournisseurs';
import type { Pagine } from '@/types/catalogue';

type Fournisseur = {
    id: number;
    nom: string;
    code: string;
    nombreProduits: number;
    creeLe: string | null;
};

/**
 * Liste des fournisseurs (route « backoffice.fournisseurs.index ») avec
 * recherche, modification et suppression (impossible si des produits y sont
 * rattachés).
 */
export default function FournisseursIndex({
    recherche,
    fournisseurs,
}: {
    recherche: string;
    fournisseurs: Pagine<Fournisseur>;
}) {
    const [saisie, setSaisie] = useState(recherche);

    return (
        <BackOfficeLayout
            title="Fournisseurs"
            actions={
                <Button
                    render={<Link href={create()} />}
                    nativeButton={false}
                    size="sm"
                >
                    <Plus />
                    <span className="hidden sm:inline">
                        Nouveau fournisseur
                    </span>
                    <span className="sm:hidden">Nouveau</span>
                </Button>
            }
        >
            <form
                onSubmit={(evenement: FormEvent) => {
                    evenement.preventDefault();
                    router.get(
                        index.url({
                            query: saisie.trim()
                                ? { recherche: saisie.trim() }
                                : {},
                        }),
                        {},
                        { preserveState: true },
                    );
                }}
                className="relative mb-4 max-w-md"
            >
                <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                <Input
                    value={saisie}
                    onChange={(evenement) => setSaisie(evenement.target.value)}
                    placeholder="Rechercher un nom ou un code…"
                    className="bg-card pl-9"
                    aria-label="Rechercher un fournisseur"
                />
            </form>

            <div className="bg-card overflow-hidden rounded-2xl border">
                {fournisseurs.data.length === 0 ? (
                    <p className="text-muted-foreground p-10 text-center text-sm">
                        Aucun fournisseur.
                    </p>
                ) : (
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-muted-foreground text-left text-xs uppercase">
                            <tr>
                                <th className="p-3 font-medium">Code</th>
                                <th className="p-3 font-medium">Nom</th>
                                <th className="p-3 text-right font-medium">
                                    Produits
                                </th>
                                <th className="hidden p-3 font-medium sm:table-cell">
                                    Créé le
                                </th>
                                <th className="p-3" />
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {fournisseurs.data.map((fournisseur) => (
                                <tr
                                    key={fournisseur.id}
                                    className="hover:bg-muted/40 transition-colors"
                                >
                                    <td className="p-3">
                                        <span className="bg-navy text-navy-foreground rounded-md px-2 py-1 font-mono text-xs font-bold tracking-widest">
                                            {fournisseur.code}
                                        </span>
                                    </td>
                                    <td className="p-3 font-medium">
                                        {fournisseur.nom}
                                    </td>
                                    <td className="p-3 text-right">
                                        {fournisseur.nombreProduits}
                                    </td>
                                    <td className="text-muted-foreground hidden p-3 sm:table-cell">
                                        {formatDate(fournisseur.creeLe)}
                                    </td>
                                    <td className="p-3">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                render={
                                                    <Link
                                                        href={edit(
                                                            fournisseur.id,
                                                        )}
                                                    />
                                                }
                                                nativeButton={false}
                                                variant="ghost"
                                                size="icon-sm"
                                                aria-label="Modifier"
                                            >
                                                <Pencil />
                                            </Button>
                                            <AlertDialog>
                                                <AlertDialogTrigger
                                                    render={
                                                        <Button
                                                            variant="ghost"
                                                            size="icon-sm"
                                                            disabled={
                                                                fournisseur.nombreProduits >
                                                                0
                                                            }
                                                            aria-label="Supprimer"
                                                            title={
                                                                fournisseur.nombreProduits >
                                                                0
                                                                    ? 'Des produits sont rattachés à ce fournisseur'
                                                                    : 'Supprimer'
                                                            }
                                                        />
                                                    }
                                                >
                                                    <Trash2 />
                                                </AlertDialogTrigger>
                                                <AlertDialogContent>
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>
                                                            Supprimer «{' '}
                                                            {fournisseur.nom} »
                                                            ?
                                                        </AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            Cette action est
                                                            définitive.
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter>
                                                        <AlertDialogCancel>
                                                            Annuler
                                                        </AlertDialogCancel>
                                                        <AlertDialogAction
                                                            variant="destructive"
                                                            onClick={() =>
                                                                router.delete(
                                                                    destroy.url(
                                                                        fournisseur.id,
                                                                    ),
                                                                    {
                                                                        preserveScroll: true,
                                                                    },
                                                                )
                                                            }
                                                        >
                                                            Supprimer
                                                        </AlertDialogAction>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>
                                            </AlertDialog>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
            <Pagination liens={fournisseurs.links} />
        </BackOfficeLayout>
    );
}
