import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/Components/ui/button';
import { FileText, Download } from 'lucide-react';

interface Categoria {
    id: number;
    name: string;
}

interface Props {
    categorias: Categoria[];
}

export default function Index({ categorias }: Props) {
    const handleDownloadGeral = () => {
        window.open('/admin/relatorios/geral', '_blank');
    };

    const handleDownloadCategoria = (categoriaId: number) => {
        window.open(`/admin/relatorios/categoria/${categoriaId}`, '_blank');
    };

    return (
        <AdminLayout title="Relatórios">
            <Head title="Relatórios" />

            <div className="space-y-6">
                {/* Relatório Geral */}
                <div className="bg-surface rounded-xl shadow-sm p-6">
                    <div className="flex items-start justify-between">
                        <div className="flex-1">
                            <div className="flex items-center gap-2 mb-2">
                                <FileText className="w-5 h-5 text-primary" />
                                <h2 className="text-lg font-semibold text-gray-900">Relatório Geral</h2>
                            </div>
                            <p className="text-sm text-gray-600 mb-4">
                                Ranking completo de todas as categorias com totalizadores gerais do evento.
                            </p>
                            <Button onClick={handleDownloadGeral} className="gap-2">
                                <Download className="w-4 h-4" />
                                Exportar PDF
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Relatórios por Categoria */}
                <div className="bg-surface rounded-xl shadow-sm p-6">
                    <div className="mb-4">
                        <h2 className="text-lg font-semibold text-gray-900 mb-1">Relatórios por Categoria</h2>
                        <p className="text-sm text-gray-600">
                            Ranking detalhado e histórico de penalidades por categoria.
                        </p>
                    </div>

                    {categorias.length === 0 ? (
                        <div className="text-center py-8 text-gray-400">
                            Nenhuma categoria cadastrada
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {categorias.map((categoria) => (
                                <div
                                    key={categoria.id}
                                    className="border border-gray-200 rounded-lg p-4 hover:border-primary/50 transition-colors"
                                >
                                    <h3 className="font-semibold text-gray-900 mb-3">{categoria.name}</h3>
                                    <Button
                                        onClick={() => handleDownloadCategoria(categoria.id)}
                                        variant="outline"
                                        size="sm"
                                        className="w-full gap-2"
                                    >
                                        <Download className="w-4 h-4" />
                                        Exportar PDF
                                    </Button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Informações */}
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div className="flex gap-3">
                        <FileText className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                        <div>
                            <h3 className="font-semibold text-blue-900 mb-1">Sobre os Relatórios</h3>
                            <ul className="text-sm text-blue-800 space-y-1">
                                <li>• Os relatórios são gerados em tempo real com os dados atuais do sistema</li>
                                <li>• O relatório geral inclui ranking de todas as categorias e estatísticas gerais</li>
                                <li>• Os relatórios por categoria incluem ranking detalhado e histórico de penalidades</li>
                                <li>• Os arquivos PDF podem ser salvos ou impressos diretamente</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
