module.exports = {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'scope-enum': [
            2,
            'always',
            [
                'restrictions',
                'inpost-pay',
                'inpost-pay-graph-ql',
                'inpost-pay-commerce',
                'restrictions-commerce',
                'inpost-pay-hyva-checkout',
                'inpost-pay-hyva-theme',
                'ci',
                'deps',
                'repo',
            ],
        ],
    },
};
