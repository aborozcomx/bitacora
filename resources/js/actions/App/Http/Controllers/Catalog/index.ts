import BranchController from './BranchController'
import EmployeeController from './EmployeeController'
import ActivityTypeController from './ActivityTypeController'
import FolioController from './FolioController'
import PaymentCatalogController from './PaymentCatalogController'
import ClientController from './ClientController'

const Catalog = {
    BranchController: Object.assign(BranchController, BranchController),
    EmployeeController: Object.assign(EmployeeController, EmployeeController),
    ActivityTypeController: Object.assign(ActivityTypeController, ActivityTypeController),
    FolioController: Object.assign(FolioController, FolioController),
    PaymentCatalogController: Object.assign(PaymentCatalogController, PaymentCatalogController),
    ClientController: Object.assign(ClientController, ClientController),
}

export default Catalog