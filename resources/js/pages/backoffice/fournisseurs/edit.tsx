import { update } from '@/actions/App/Http/Controllers/BackOffice/FournisseurController';
import { FournisseurForm } from '@/components/fournisseur-form';
import BackOfficeLayout from '@/layouts/back-office-layout';
import { pluriel } from '@/lib/format';

/**
 * Modification d'un fournisseur (route "backoffice.fournisseurs.edit").
 */
export default function EditFournisseur({
    fournisseur,
}: {
    fournisseur: {
        id: number;
        nom: string;
        code: string;
        nombreProduits: number;
    };
}) {
    return (
        <BackOfficeLayout title={`Fournisseur ${fournisseur.nom}`}>
            <div className="bg-card rounded-3xl border p-6">
                <p className="text-muted-foreground mb-6 text-sm">
                    {pluriel(
                        fournisseur.nombreProduits,
                        'produit rattaché',
                        'produits rattachés',
                    )}
                </p>
                <FournisseurForm
                    action={update.url(fournisseur.id)}
                    method="put"
                    valeurs={{ nom: fournisseur.nom, code: fournisseur.code }}
                    codeVerrouille={fournisseur.nombreProduits > 0}
                    libelleBouton="Enregistrer"
                />
            </div>
        </BackOfficeLayout>
    );
}
