import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
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

const paymentMethods = {
    index: Object.assign(index, index),
    storeMethod: Object.assign(storeMethod, storeMethod),
    updateMethod: Object.assign(updateMethod, updateMethod),
    destroyMethod: Object.assign(destroyMethod, destroyMethod),
    storeCardType: Object.assign(storeCardType, storeCardType),
    updateCardType: Object.assign(updateCardType, updateCardType),
    destroyCardType: Object.assign(destroyCardType, destroyCardType),
    storeCard: Object.assign(storeCard, storeCard),
    updateCard: Object.assign(updateCard, updateCard),
    destroyCard: Object.assign(destroyCard, destroyCard),
}

export default paymentMethods