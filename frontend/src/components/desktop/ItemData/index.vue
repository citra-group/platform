<template>
    <tr>
        <td>
            <v-checkbox-btn
                :model-value="isSelected(internalItem)"
                :color="theme"
                @update:model-value="toggleSelect(internalItem)"
            ></v-checkbox-btn>
        </td>

        <slot
            :headers="headers"
            :item="item"
            :index="index"
            :internalItem="internalItem"
            :isSelected="isSelected"
            :toggleSelect="toggleSelect"
        >
            <td
                v-for="(column, colIndex) in headers"
                :key="colIndex"
                @click="toggleSelect(internalItem)"
            >
                {{ item[column.value] }}
            </td>
        </slot>
    </tr>
</template>

<script>
import { usePageStore } from "@pinia/pageStore";
import { storeToRefs } from "pinia";
export default {
    name: "item-data",

    props: {
        headers: Array,
        item: Object,
        index: Number,
        internalItem: Object,
        isSelected: Function,
        toggleSelect: Function,
    },

    setup() {
        const store = usePageStore();

        const { theme } = storeToRefs(store);

        return {
            theme,
        };
    },
};
</script>
