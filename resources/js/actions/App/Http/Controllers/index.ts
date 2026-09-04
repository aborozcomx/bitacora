import BitacoraController from './BitacoraController'
import SalaryReportController from './SalaryReportController'
import ExpenseReportController from './ExpenseReportController'
import Catalog from './Catalog'
import Admin from './Admin'
import Settings from './Settings'

const Controllers = {
    BitacoraController: Object.assign(BitacoraController, BitacoraController),
    SalaryReportController: Object.assign(SalaryReportController, SalaryReportController),
    ExpenseReportController: Object.assign(ExpenseReportController, ExpenseReportController),
    Catalog: Object.assign(Catalog, Catalog),
    Admin: Object.assign(Admin, Admin),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers