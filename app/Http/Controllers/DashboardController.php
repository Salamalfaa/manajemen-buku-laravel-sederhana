<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalBooks = Buku::count();
        $totalAuthors = Buku::query()->distinct('penulis')->count('penulis');
        $totalUsers = User::count();
        $roles = [
            ['name' => 'Admin', 'label' => 'Administrator', 'color' => '#6d79f6', 'description' => 'Kelola buku dan akses akun'],
            ['name' => 'Dosen', 'label' => 'Dosen', 'color' => '#a777ec', 'description' => 'Tambah dan perbarui koleksi'],
            ['name' => 'Mahasiswa', 'label' => 'Mahasiswa', 'color' => '#46b98c', 'description' => 'Jelajahi koleksi buku'],
        ];

        $roleDistribution = collect($roles)
            ->map(function (array $role) use ($totalUsers): array {
                $count = User::role($role['name'])->count();

                return [
                    ...$role,
                    'count' => $count,
                    'percentage' => $totalUsers > 0 ? $count / $totalUsers * 100 : 0,
                ];
            });

        $roleChart = $this->buildRoleChart($roleDistribution, $totalUsers);

        $yearDistribution = Buku::query()
            ->selectRaw('tahun_terbit, COUNT(*) as total')
            ->groupBy('tahun_terbit')
            ->orderByDesc('tahun_terbit')
            ->limit(5)
            ->get()
            ->map(fn ($year): array => [
                'year' => $year->tahun_terbit,
                'count' => (int) $year->total,
                'percentage' => $totalBooks > 0 ? (int) $year->total / $totalBooks * 100 : 0,
            ]);

        $largestRole = $roleDistribution->sortByDesc('count')->first();

        return view('dashboard', [
            'totalBooks' => $totalBooks,
            'totalAuthors' => $totalAuthors,
            'totalUsers' => $totalUsers,
            'latestPublicationYear' => Buku::max('tahun_terbit'),
            'roleDistribution' => $roleDistribution,
            'roleChart' => $roleChart,
            'yearDistribution' => $yearDistribution,
            'largestRole' => $largestRole,
        ]);
    }

    /**
     * @param Collection<int, array{name: string, label: string, color: string, description: string, count: int, percentage: float}> $roles
     */
    private function buildRoleChart(Collection $roles, int $totalUsers): string
    {
        if ($totalUsers === 0) {
            return '#e6eaf1 0% 100%';
        }

        $position = 0.0;
        $segments = [];

        foreach ($roles as $role) {
            if ($role['count'] === 0) {
                continue;
            }

            $start = $position;
            $position += $role['percentage'];
            $segments[] = "{$role['color']} {$start}% {$position}%";
        }

        return implode(', ', $segments);
    }
}
