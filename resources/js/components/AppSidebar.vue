<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    ClipboardList,
    Calculator,
    Receipt,
    Building,
    Users,
    CreditCard,
    ListCheck,
    FileDigit,
    UserCheck,
    ShieldCheck
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <!-- Operación Principal -->
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Gestión Principal</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/dashboard')">
                            <Link href="/dashboard">
                                <LayoutGrid class="h-4 w-4" />
                                <span>Dashboard</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/bitacoras')">
                            <Link href="/bitacoras">
                                <ClipboardList class="h-4 w-4" />
                                <span>Bitácoras</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/salaries')">
                            <Link href="/salaries">
                                <Calculator class="h-4 w-4" />
                                <span>Cálculo de Salarios</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/expenses')">
                            <Link href="/expenses">
                                <Receipt class="h-4 w-4" />
                                <span>Reporte de Gastos</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- Catálogos Autoadministrables -->
            <SidebarGroup class="px-2 py-2">
                <SidebarGroupLabel>Catálogos</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/catalogs/branches')">
                            <Link href="/catalogs/branches">
                                <Building class="h-4 w-4" />
                                <span>Sucursales</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/catalogs/employees')">
                            <Link href="/catalogs/employees">
                                <Users class="h-4 w-4" />
                                <span>Empleados</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/catalogs/clients')">
                            <Link href="/catalogs/clients">
                                <Users class="h-4 w-4" />
                                <span>Clientes</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/catalogs/payment-methods')">
                            <Link href="/catalogs/payment-methods">
                                <CreditCard class="h-4 w-4" />
                                <span>Métodos y Tarjetas</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/catalogs/activities')">
                            <Link href="/catalogs/activities">
                                <ListCheck class="h-4 w-4" />
                                <span>Actividades</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/catalogs/folios')">
                            <Link href="/catalogs/folios">
                                <FileDigit class="h-4 w-4" />
                                <span>Folios</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- Administración de Accesos -->
            <SidebarGroup class="px-2 py-2">
                <SidebarGroupLabel>Administración</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/admin/users')">
                            <Link href="/admin/users">
                                <UserCheck class="h-4 w-4" />
                                <span>Usuarios</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="isCurrentUrl('/admin/roles')">
                            <Link href="/admin/roles">
                                <ShieldCheck class="h-4 w-4" />
                                <span>Roles y Permisos</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
