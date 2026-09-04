import branches from './branches'
import employees from './employees'
import activities from './activities'
import folios from './folios'
import paymentMethods from './payment-methods'
import clients from './clients'

const catalogs = {
    branches: Object.assign(branches, branches),
    employees: Object.assign(employees, employees),
    activities: Object.assign(activities, activities),
    folios: Object.assign(folios, folios),
    paymentMethods: Object.assign(paymentMethods, paymentMethods),
    clients: Object.assign(clients, clients),
}

export default catalogs