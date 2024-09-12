import PackagesPricingCards from "@/Components/PackagesPricingCards";
import { Head } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Index({
    auth,
    packages,
    features,
    success,
    error,
    testreturn,
}) {
    const availableCredits = auth.user.available_credits;
    testreturn && console.log(testreturn);
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Your Credits
                </h2>
            }
        >
            <Head title="Your Credits" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {success !== null && (
                        <div className="mb-3 py-5 px-5 rounded text-white bg-emerald-600 text-xl">
                            {success}
                        </div>
                    )}
                    {error !== null && (
                        <div className="mb-3 py-5 px-5 rounded text-white bg-red-500 text-xl">
                            {error}
                        </div>
                    )}
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg relative">
                        <div className="flex flex-col gap-3 items-center p-4">
                            <img
                                src="./images/coin.png"
                                alt=""
                                className="w-[100px]"
                            />

                            <h3 className="text-white text-2xl">
                                You have {availableCredits} credits.
                            </h3>
                        </div>
                    </div>
                    <PackagesPricingCards
                        packages={packages.data}
                        features={features.data}
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
