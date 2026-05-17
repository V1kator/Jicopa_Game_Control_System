import { useState } from 'react';
import {
    useReactTable,
    getCoreRowModel,
    getExpandedRowModel,
    ColumnDef,
    flexRender,
} from '@tanstack/react-table';
import { Card, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import PointBreakdown from './PointBreakdown';

interface Turma {
    id: number;
    name: string;
    period: 'Matutino' | 'Vespertino';
}

interface Score {
    pontos_vitorias: number;
    pontos_empates: number;
    pontos_individuais: number;
    pontos_avaliacao_base: number;
    bonus_avaliacao: number;
    pontos_avaliacao: number;
    penalidades: number;
    total: number;
    saldo_gols: number;
}

interface RankingItem {
    posicao: number;
    turma: Turma;
    score: Score;
}

interface Props {
    data: RankingItem[];
    categoriaAtual: number | null;
}

export default function RankingTable({ data, categoriaAtual }: Props) {
    const [expanded, setExpanded] = useState({});
    
    const columns: ColumnDef<RankingItem>[] = [
        {
            accessorKey: 'posicao',
            header: 'Pos',
            cell: ({ row }) => (
                <div className="font-bold text-lg">{row.original.posicao}º</div>
            ),
        },
        {
            accessorKey: 'turma.name',
            header: 'Turma',
            cell: ({ row }) => (
                <div>
                    <div className="font-semibold">{row.original.turma.name}</div>
                    <Badge variant="outline" className="mt-1">
                        {row.original.turma.period}
                    </Badge>
                </div>
            ),
        },
        {
            accessorKey: 'score.total',
            header: 'Pontos',
            cell: ({ row }) => (
                <div className="text-xl font-bold text-primary">
                    {row.original.score.total}
                </div>
            ),
        },
        {
            accessorKey: 'score.saldo_gols',
            header: 'Saldo',
            cell: ({ row }) => {
                const saldo = row.original.score.saldo_gols;
                const color = saldo > 0 ? 'text-green-600' : saldo < 0 ? 'text-red-600' : 'text-gray-600';
                return (
                    <div className={`font-semibold ${color}`}>
                        {saldo > 0 ? '+' : ''}{saldo}
                    </div>
                );
            },
        },
        {
            id: 'expand',
            header: 'Detalhes',
            cell: ({ row }) => (
                <button
                    onClick={() => row.toggleExpanded()}
                    className="text-primary hover:text-primary/80 font-medium"
                >
                    {row.getIsExpanded() ? '▼ Ocultar' : '▶ Ver breakdown'}
                </button>
            ),
        },
        {
            id: 'actions',
            header: 'Ações',
            cell: ({ row }) => (
                <a
                    href={`/admin/turmas/${row.original.turma.id}/boletim?categoria_id=${categoriaAtual}`}
                    className="text-blue-600 hover:text-blue-800 text-sm"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    📊 Boletim
                </a>
            ),
        },
    ];
    
    const table = useReactTable({
        data,
        columns,
        state: {
            expanded,
        },
        onExpandedChange: setExpanded,
        getCoreRowModel: getCoreRowModel(),
        getExpandedRowModel: getExpandedRowModel(),
    });
    
    return (
        <Card>
            <CardContent className="p-0">
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="bg-gray-50 border-b">
                            {table.getHeaderGroups().map((headerGroup) => (
                                <tr key={headerGroup.id}>
                                    {headerGroup.headers.map((header) => (
                                        <th
                                            key={header.id}
                                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            {flexRender(header.column.columnDef.header, header.getContext())}
                                        </th>
                                    ))}
                                </tr>
                            ))}
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {table.getRowModel().rows.map((row) => (
                                <>
                                    <tr key={row.id} className="hover:bg-gray-50">
                                        {row.getVisibleCells().map((cell) => (
                                            <td key={cell.id} className="px-6 py-4 whitespace-nowrap">
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </td>
                                        ))}
                                    </tr>
                                    {row.getIsExpanded() && (
                                        <tr key={`${row.id}-expanded`}>
                                            <td colSpan={columns.length} className="px-6 py-4 bg-gray-50">
                                                <PointBreakdown score={row.original.score} />
                                            </td>
                                        </tr>
                                    )}
                                </>
                            ))}
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    );
}
