import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:17
* @route '/salaries'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/salaries',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:17
* @route '/salaries'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:17
* @route '/salaries'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:17
* @route '/salaries'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

const salaries = {
    index: Object.assign(index, index),
}

export default salaries