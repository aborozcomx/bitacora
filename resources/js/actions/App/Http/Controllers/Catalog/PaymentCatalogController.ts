import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::index
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:17
* @route '/catalogs/payment-methods'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/catalogs/payment-methods',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::index
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:17
* @route '/catalogs/payment-methods'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::index
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:17
* @route '/catalogs/payment-methods'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::index
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:17
* @route '/catalogs/payment-methods'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::index
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:17
* @route '/catalogs/payment-methods'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::index
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:17
* @route '/catalogs/payment-methods'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::index
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:17
* @route '/catalogs/payment-methods'
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

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:27
* @route '/catalogs/payment-methods/method'
*/
export const storeMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeMethod.url(options),
    method: 'post',
})

storeMethod.definition = {
    methods: ["post"],
    url: '/catalogs/payment-methods/method',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:27
* @route '/catalogs/payment-methods/method'
*/
storeMethod.url = (options?: RouteQueryOptions) => {
    return storeMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:27
* @route '/catalogs/payment-methods/method'
*/
storeMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeMethod.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:27
* @route '/catalogs/payment-methods/method'
*/
const storeMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeMethod.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:27
* @route '/catalogs/payment-methods/method'
*/
storeMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeMethod.url(options),
    method: 'post',
})

storeMethod.form = storeMethodForm

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:42
* @route '/catalogs/payment-methods/method/{method}'
*/
export const updateMethod = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateMethod.url(args, options),
    method: 'put',
})

updateMethod.definition = {
    methods: ["put"],
    url: '/catalogs/payment-methods/method/{method}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:42
* @route '/catalogs/payment-methods/method/{method}'
*/
updateMethod.url = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { method: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { method: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            method: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        method: typeof args.method === 'object'
        ? args.method.id
        : args.method,
    }

    return updateMethod.definition.url
            .replace('{method}', parsedArgs.method.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:42
* @route '/catalogs/payment-methods/method/{method}'
*/
updateMethod.put = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateMethod.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:42
* @route '/catalogs/payment-methods/method/{method}'
*/
const updateMethodForm = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateMethod.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:42
* @route '/catalogs/payment-methods/method/{method}'
*/
updateMethodForm.put = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateMethod.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateMethod.form = updateMethodForm

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:56
* @route '/catalogs/payment-methods/method/{method}'
*/
export const destroyMethod = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyMethod.url(args, options),
    method: 'delete',
})

destroyMethod.definition = {
    methods: ["delete"],
    url: '/catalogs/payment-methods/method/{method}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:56
* @route '/catalogs/payment-methods/method/{method}'
*/
destroyMethod.url = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { method: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { method: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            method: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        method: typeof args.method === 'object'
        ? args.method.id
        : args.method,
    }

    return destroyMethod.definition.url
            .replace('{method}', parsedArgs.method.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:56
* @route '/catalogs/payment-methods/method/{method}'
*/
destroyMethod.delete = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyMethod.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:56
* @route '/catalogs/payment-methods/method/{method}'
*/
const destroyMethodForm = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyMethod.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyMethod
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:56
* @route '/catalogs/payment-methods/method/{method}'
*/
destroyMethodForm.delete = (args: { method: number | { id: number } } | [method: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyMethod.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyMethod.form = destroyMethodForm

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:64
* @route '/catalogs/payment-methods/card-type'
*/
export const storeCardType = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCardType.url(options),
    method: 'post',
})

storeCardType.definition = {
    methods: ["post"],
    url: '/catalogs/payment-methods/card-type',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:64
* @route '/catalogs/payment-methods/card-type'
*/
storeCardType.url = (options?: RouteQueryOptions) => {
    return storeCardType.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:64
* @route '/catalogs/payment-methods/card-type'
*/
storeCardType.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCardType.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:64
* @route '/catalogs/payment-methods/card-type'
*/
const storeCardTypeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCardType.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:64
* @route '/catalogs/payment-methods/card-type'
*/
storeCardTypeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCardType.url(options),
    method: 'post',
})

storeCardType.form = storeCardTypeForm

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:76
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
export const updateCardType = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateCardType.url(args, options),
    method: 'put',
})

updateCardType.definition = {
    methods: ["put"],
    url: '/catalogs/payment-methods/card-type/{cardType}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:76
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
updateCardType.url = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cardType: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { cardType: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            cardType: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        cardType: typeof args.cardType === 'object'
        ? args.cardType.id
        : args.cardType,
    }

    return updateCardType.definition.url
            .replace('{cardType}', parsedArgs.cardType.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:76
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
updateCardType.put = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateCardType.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:76
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
const updateCardTypeForm = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateCardType.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:76
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
updateCardTypeForm.put = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateCardType.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateCardType.form = updateCardTypeForm

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:88
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
export const destroyCardType = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyCardType.url(args, options),
    method: 'delete',
})

destroyCardType.definition = {
    methods: ["delete"],
    url: '/catalogs/payment-methods/card-type/{cardType}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:88
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
destroyCardType.url = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cardType: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { cardType: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            cardType: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        cardType: typeof args.cardType === 'object'
        ? args.cardType.id
        : args.cardType,
    }

    return destroyCardType.definition.url
            .replace('{cardType}', parsedArgs.cardType.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:88
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
destroyCardType.delete = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyCardType.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:88
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
const destroyCardTypeForm = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyCardType.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCardType
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:88
* @route '/catalogs/payment-methods/card-type/{cardType}'
*/
destroyCardTypeForm.delete = (args: { cardType: number | { id: number } } | [cardType: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyCardType.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyCardType.form = destroyCardTypeForm

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:96
* @route '/catalogs/payment-methods/card'
*/
export const storeCard = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCard.url(options),
    method: 'post',
})

storeCard.definition = {
    methods: ["post"],
    url: '/catalogs/payment-methods/card',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:96
* @route '/catalogs/payment-methods/card'
*/
storeCard.url = (options?: RouteQueryOptions) => {
    return storeCard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:96
* @route '/catalogs/payment-methods/card'
*/
storeCard.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCard.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:96
* @route '/catalogs/payment-methods/card'
*/
const storeCardForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCard.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::storeCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:96
* @route '/catalogs/payment-methods/card'
*/
storeCardForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCard.url(options),
    method: 'post',
})

storeCard.form = storeCardForm

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:112
* @route '/catalogs/payment-methods/card/{card}'
*/
export const updateCard = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateCard.url(args, options),
    method: 'put',
})

updateCard.definition = {
    methods: ["put"],
    url: '/catalogs/payment-methods/card/{card}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:112
* @route '/catalogs/payment-methods/card/{card}'
*/
updateCard.url = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { card: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { card: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            card: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        card: typeof args.card === 'object'
        ? args.card.id
        : args.card,
    }

    return updateCard.definition.url
            .replace('{card}', parsedArgs.card.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:112
* @route '/catalogs/payment-methods/card/{card}'
*/
updateCard.put = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateCard.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:112
* @route '/catalogs/payment-methods/card/{card}'
*/
const updateCardForm = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateCard.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::updateCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:112
* @route '/catalogs/payment-methods/card/{card}'
*/
updateCardForm.put = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateCard.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateCard.form = updateCardForm

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:128
* @route '/catalogs/payment-methods/card/{card}'
*/
export const destroyCard = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyCard.url(args, options),
    method: 'delete',
})

destroyCard.definition = {
    methods: ["delete"],
    url: '/catalogs/payment-methods/card/{card}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:128
* @route '/catalogs/payment-methods/card/{card}'
*/
destroyCard.url = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { card: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { card: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            card: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        card: typeof args.card === 'object'
        ? args.card.id
        : args.card,
    }

    return destroyCard.definition.url
            .replace('{card}', parsedArgs.card.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:128
* @route '/catalogs/payment-methods/card/{card}'
*/
destroyCard.delete = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyCard.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:128
* @route '/catalogs/payment-methods/card/{card}'
*/
const destroyCardForm = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyCard.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\PaymentCatalogController::destroyCard
* @see app/Http/Controllers/Catalog/PaymentCatalogController.php:128
* @route '/catalogs/payment-methods/card/{card}'
*/
destroyCardForm.delete = (args: { card: number | { id: number } } | [card: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyCard.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyCard.form = destroyCardForm

const PaymentCatalogController = { index, storeMethod, updateMethod, destroyMethod, storeCardType, updateCardType, destroyCardType, storeCard, updateCard, destroyCard }

export default PaymentCatalogController