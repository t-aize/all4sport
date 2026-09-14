import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome" />
            <div className="bg-background text-foreground flex min-h-screen flex-col items-center justify-center gap-4">
                <h1 className="text-2xl font-medium">Hello World</h1>
                <Button>Button</Button>
            </div>
        </>
    );
}
