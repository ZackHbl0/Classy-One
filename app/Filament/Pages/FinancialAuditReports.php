<?php

namespace App\Filament\Pages;

use App\Models\Paiement;
use App\Services\PaymentService;
use Filament\Pages\Page;

class FinancialAuditReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Gestion Financière';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Audit & Rapports Financiers';
    protected static ?string $title = 'Audit & Rapports Financiers';
    protected static string $view = 'filament.pages.financial-audit-reports';

    public int $selectedYear;
    public ?string $selectedMonth = '';

    public function mount(): void
    {
        $this->selectedYear = (int) date('Y');
        $this->selectedMonth = (string) (int) date('m');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getAvailableYearsProperty(): array
    {
        $dbYears = Paiement::selectRaw('YEAR(dateEcheance) as yr')
            ->whereNotNull('dateEcheance')
            ->distinct()
            ->pluck('yr')
            ->filter()
            ->map(fn($y) => (int) $y)
            ->toArray();

        $currentYear = (int) date('Y');
        $defaultRange = range($currentYear + 2, $currentYear - 3);

        $years = array_unique(array_merge($defaultRange, $dbYears));
        rsort($years);

        return $years;
    }

    public function getMonthsListProperty(): array
    {
        return [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
        ];
    }

    public function getFinancialDataProperty(): array
    {
        $month = (!empty($this->selectedMonth) && is_numeric($this->selectedMonth)) 
            ? (int) $this->selectedMonth 
            : null;

        return app(PaymentService::class)->getFinancialSummary($this->selectedYear, $month);
    }

    public function filterByMonth(int $monthNum): void
    {
        if ((string) $this->selectedMonth === (string) $monthNum) {
            $this->selectedMonth = ''; // Toggle off to view full year
        } else {
            $this->selectedMonth = (string) $monthNum;
        }
    }
}
