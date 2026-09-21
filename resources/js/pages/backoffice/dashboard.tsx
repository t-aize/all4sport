import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import BackOfficeLayout from '@/layouts/back-office-layout';

/**
 * Un état de commande tel que l'envoie DashboardController (valeur technique
 * de l'enum PHP EtatCommande et libellé à afficher).
 */
type EtatGerable = {
    value: string;
    label: string;
};

/**
 * Tableau de bord du back-office (route « backoffice.dashboard »).
 *
 * Rappelle au collaborateur son rôle et les états de commande qu'il peut
 * enregistrer. Les écrans de gestion des commandes viendront s'y rattacher.
 *
 * @param roleLibelle Nom du rôle de l'employé.
 * @param etatsGerables États de commande que ce rôle a le droit d'enregistrer.
 */
export default function Dashboard({
    roleLibelle,
    etatsGerables,
}: {
    roleLibelle: string;
    etatsGerables: EtatGerable[];
}) {
    return (
        <BackOfficeLayout title="Tableau de bord" roleLibelle={roleLibelle}>
            <Card>
                <CardHeader>
                    <CardTitle>Vos responsabilités</CardTitle>
                    <CardDescription>
                        En tant que {roleLibelle.toLowerCase()}, vous pouvez
                        enregistrer les états de commande suivants.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ul className="flex flex-col gap-2">
                        {etatsGerables.map((etat) => (
                            <li
                                key={etat.value}
                                className="bg-muted rounded-md px-3 py-2 text-sm font-medium"
                            >
                                {etat.label}
                            </li>
                        ))}
                    </ul>
                </CardContent>
            </Card>
        </BackOfficeLayout>
    );
}
