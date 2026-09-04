import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:15
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
* @see app/Http/Controllers/SalaryReportController.php:15
* @route '/salaries'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:15
* @route '/salaries'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:15
* @route '/salaries'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:15
* @route '/salaries'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:15
* @route '/salaries'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalaryReportController::index
* @see app/Http/Controllers/SalaryReportController.php:15
* @route '/salaries'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

const SalaryReportController = { index }

export default SalaryReportController