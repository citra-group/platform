<template>
    <v-text-field
        :hide-details="hideDetails"
        :label="label"
        :prefix="prefix"
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
        prefix: String,
        hideDetails: Boolean,
        label: String,
        modelValue: Number,
        precision: [Number, String],
        readonly: Boolean,
        minRange: [Number, String],
        maxRange: [Number, String],
    },

    setup(props) {
        const valueRange = { min: 0 };

        if (props.minRange) {
            valueRange["min"] = parseInt(props.minRange);
        }

        if (props.maxRange) {
            valueRange["max"] = parseInt(props.maxRange);
        }

        const { formattedValue, numberValue, inputRef, setValue } =
            useCurrencyInput({
                currency: "IDR",
                locale: "id",
                autoDecimalDigits: true,
                currencyDisplay: "hidden",
                hideGroupingSeparatorOnFocus: false,
                precision: parseInt(props.precision),
                valueRange: valueRange,
            });

        return { formattedValue, numberValue, inputRef, setValue };
    },

    watch: {
        modelValue: function (value) {
            this.setValue(value);
        },
    },
};
</script>

<style>
.v-input--currency .v-field__input {
    text-align: right;
}
</style>
