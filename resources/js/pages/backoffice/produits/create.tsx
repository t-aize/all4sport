import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ProduitForm, {
    type MagasinStock,
    type Option,
} from '@/components/produit-form';
import BackOfficeLayout from '@/layouts/back-office-layout';
import { store } from '@/actions/App/Http/Controllers/BackOffice/ProduitController';

/**
 * Création d'un produit (route "backoffice.produits.create").
 *
 * La référence du produit n'est pas saisie : elle est générée à
 * l'enregistrement à partir des codes du fournisseur et du rayon.
 *
 * @param roleLibelle Nom du rôle de l'employé connecté.
 * @param fournisseurs Fournisseurs proposés.
 * @param rayons Rayons proposés.
 * @param magasins Magasins dont on saisit le stock.
 */
export default function CreateProduit({
    roleLibelle,
    fournisseurs,
    rayons,
    magasins,
}: {
    roleLibelle: string;
    fournisseurs: Option[];
    rayons: Option[];
    magasins: MagasinStock[];
}) {
    const formulaire = store.form();

    return (
        <BackOfficeLayout title="Nouveau produit" roleLibelle={roleLibelle}>
            <Card>
                <CardHeader>
                    <CardTitle>Nouveau produit</CardTitle>
                    <CardDescription>
                        La référence est générée automatiquement à
                        l'enregistrement.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ProduitForm
                        action={formulaire.action}
                        method="post"
                        fournisseurs={fournisseurs}
                        rayons={rayons}
                        magasins={magasins}
                        libelleBouton="Créer le produit"
                    />
                </CardContent>
            </Card>
        </BackOfficeLayout>
    );
}
