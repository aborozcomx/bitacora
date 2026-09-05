<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CardType;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\Employee;
use App\Models\Folio;
use App\Models\PaymentCard;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Branches
        $branch1 = Branch::withTrashed()->firstOrCreate(
            ['code' => 'SUC-001'],
            [
                'name' => 'Sucursal Centro',
                'address' => 'Av. Juárez 100, Centro',
                'phone' => '555-100-2000',
                'is_active' => true,
            ]
        );
        if ($branch1->trashed()) {
            $branch1->restore();
        }

        $branch2 = Branch::withTrashed()->firstOrCreate(
            ['code' => 'SUC-002'],
            [
                'name' => 'Sucursal Norte',
                'address' => 'Blvd. Industria 450, Norte',
                'phone' => '555-200-3000',
                'is_active' => true,
            ]
        );
        if ($branch2->trashed()) {
            $branch2->restore();
        }

        // Manager User
        $managerUser = User::firstOrCreate(
            ['email' => 'encargado.centro@empresa.com'],
            [
                'name' => 'Carlos Encargado Centro',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $managerUser->assignRole('encargado');
        $managerUser->branches()->sync([$branch1->id]);

        // 2. Employees
        Employee::firstOrCreate(
            ['employee_code' => 'EMP-001'],
            [
                'branch_id' => $branch1->id,
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'base_hourly_rate' => 120.00,
                'overtime_hourly_rate' => 180.00,
                'is_active' => true,
            ]
        );

        Employee::firstOrCreate(
            ['employee_code' => 'EMP-002'],
            [
                'branch_id' => $branch1->id,
                'first_name' => 'María',
                'last_name' => 'Gómez',
                'base_hourly_rate' => 130.00,
                'overtime_hourly_rate' => 195.00,
                'is_active' => true,
            ]
        );

        Employee::firstOrCreate(
            ['employee_code' => 'EMP-003'],
            [
                'branch_id' => $branch2->id,
                'first_name' => 'Roberto',
                'last_name' => 'López',
                'base_hourly_rate' => 115.00,
                'overtime_hourly_rate' => 172.50,
                'is_active' => true,
            ]
        );

        // 3. Payment Methods
        $efectivo = PaymentMethod::firstOrCreate(
            ['slug' => 'efectivo'],
            ['name' => 'Efectivo', 'requires_card_details' => false, 'is_active' => true]
        );

        $transferencia = PaymentMethod::firstOrCreate(
            ['slug' => 'transferencia'],
            ['name' => 'Transferencia Bancaria', 'requires_card_details' => false, 'is_active' => true]
        );

        $debito = PaymentMethod::firstOrCreate(
            ['slug' => 'tarjeta-debito'],
            ['name' => 'Tarjeta de Débito', 'requires_card_details' => true, 'is_active' => true]
        );

        $credito = PaymentMethod::firstOrCreate(
            ['slug' => 'tarjeta-credito'],
            ['name' => 'Tarjeta de Crédito', 'requires_card_details' => true, 'is_active' => true]
        );

        // 4. Card Types
        $visa = CardType::firstOrCreate(['name' => 'Visa']);
        $mastercard = CardType::firstOrCreate(['name' => 'Mastercard']);
        $amex = CardType::firstOrCreate(['name' => 'American Express']);

        // 5. Payment Cards / Accounts
        PaymentCard::firstOrCreate(
            ['alias' => 'BBVA Débito Operaciones'],
            [
                'payment_method_id' => $debito->id,
                'card_type_id' => $visa->id,
                'bank_name' => 'BBVA',
                'card_number_masked' => '**** **** **** 4321',
                'is_active' => true,
            ]
        );

        PaymentCard::firstOrCreate(
            ['alias' => 'Santander Crédito Corporativa'],
            [
                'payment_method_id' => $credito->id,
                'card_type_id' => $mastercard->id,
                'bank_name' => 'Santander',
                'card_number_masked' => '**** **** **** 8899',
                'is_active' => true,
            ]
        );

        PaymentCard::firstOrCreate(
            ['alias' => 'Cuenta Maestra BBVA Transferencias'],
            [
                'payment_method_id' => $transferencia->id,
                'card_type_id' => null,
                'bank_name' => 'BBVA',
                'card_number_masked' => 'CLABE **** **** **** 9012',
                'is_active' => true,
            ]
        );

        // 7. Clients & Client Branches
        $client1 = Client::firstOrCreate(
            ['code' => 'CLI-001'],
            [
                'name' => 'Industrias Metálicas del Norte S.A.',
                'is_active' => true,
            ]
        );

        ClientBranch::firstOrCreate(
            ['client_id' => $client1->id, 'name' => 'Planta Monterrey Poniente'],
            [
                'code' => 'MTY-PTE',
                'address' => 'Parque Industrial Mitras Lote 4',
                'contact_name' => 'Lic. Roberto Garza',
                'phone' => '818-123-4568',
                'email' => 'rgarza@imnorte.com',
                'is_active' => true,
            ]
        );

        ClientBranch::firstOrCreate(
            ['client_id' => $client1->id, 'name' => 'Cedis Apodaca'],
            [
                'code' => 'APO-01',
                'address' => 'Carretera a Miguel Alemán km 14',
                'contact_name' => 'Ing. Laura Serna',
                'phone' => '818-123-4569',
                'email' => 'lserna@imnorte.com',
                'is_active' => true,
            ]
        );

        $client2 = Client::firstOrCreate(
            ['code' => 'CLI-002'],
            [
                'name' => 'Comercializadora y Distribución San Ángel',
                'is_active' => true,
            ]
        );

        ClientBranch::firstOrCreate(
            ['client_id' => $client2->id, 'name' => 'Sucursal Insurgentes Sur'],
            [
                'code' => 'INS-SUR',
                'address' => 'Av. Insurgentes Sur 1200',
                'contact_name' => 'Ing. Diana Valdés',
                'phone' => '555-987-6544',
                'email' => 'dvaldes@sanangel.com.mx',
                'is_active' => true,
            ]
        );

        // 8. Folio Series
        $defaultFolios = [
            ['name' => 'BIT', 'description' => 'Bitácoras Generales', 'current_consecutive' => 0],
            ['name' => 'ICC', 'description' => 'Ingeniería y Control de Calidad', 'current_consecutive' => 0],
            ['name' => 'SERV', 'description' => 'Servicios Técnicos Especializados', 'current_consecutive' => 0],
            ['name' => 'MANT', 'description' => 'Mantenimiento Preventivo y Correctivo', 'current_consecutive' => 0],
            ['name' => 'OBRA', 'description' => 'Proyectos y Obras', 'current_consecutive' => 0],
        ];

        foreach ($defaultFolios as $f) {
            Folio::firstOrCreate(
                ['name' => $f['name']],
                [
                    'description' => $f['description'],
                    'current_consecutive' => $f['current_consecutive'],
                    'is_active' => true,
                ]
            );
        }
    }
}
