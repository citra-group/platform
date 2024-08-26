<template>
    <v-text-field
        :hide-details="hideDetails"
        :label="label"
        :readonly="readonly"
        class="v-input--currency"
        ref="inputRef"
        v-model="formattedValue"
    ></v-text-field>
</template>

<script>
import { useCurrencyInput } from "vue-currency-input";

export default {
    name: "CurrencyInput",

    props: {
        hideDetails: Boolean,
        label: String,
        modelValue: Number,
        precision: [Number, String],
        readonly: Boolean,
    },

    setup(props) {
        const { formattedValue, numberValue, inputRef } = useCurrencyInput({
            currency: "IDR",
            locale: "id",
            autoDecimalDigits: true,
            currencyDisplay: "hidden",
            hideGroupingSeparatorOnFocus: false,
            precision: parseInt(props.precision),
            valueRange: { min: 0 },
        });

        return { formattedValue, numberValue, inputRef };
    },

    methods: {
        updateModelValue: function () {
            console.log(this.numberValue);
        },
    },
};
</script>

<style>
.v-input--currency .v-field__input {
    text-align: right;
}
</style>
