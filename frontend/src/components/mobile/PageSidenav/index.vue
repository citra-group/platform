<template>
    <v-navigation-drawer
        :color="`${theme}-lighten-4`"
        location="right"
        width="360"
        v-model="sidenavState"
        disable-resize-watcher
        style="height: 100%; top: 0; z-index: 1009"
    >
        <v-sheet class="position-relative" color="transparent" height="100dvh">
            <v-toolbar :color="theme">
                <v-btn icon @click="sidenavState = false">
                    <v-icon>close</v-icon>
                </v-btn>

                <v-toolbar-title class="text-white text-overline">
                    {{ title }}
                </v-toolbar-title>

                <v-spacer></v-spacer>
            </v-toolbar>

            <v-sheet
                :color="`${theme}`"
                class="mx-auto position-absolute rounded-b-xl"
                height="192"
                width="360"
            ></v-sheet>

            <v-responsive
                height="calc(100dvh - 64px)"
                class="bg-transparent overflow-x-hidden overflow-y-auto scrollbar-none px-4"
                content-class="position-relative"
            >
                <div
                    class="position-absolute text-center w-100"
                    style="z-index: 1"
                >
                    <div
                        class="d-flex flex-column align-center justify-center position-relative"
                    >
                        <form-icon icon="filter_list"></form-icon>
                    </div>
                </div>

                <v-sheet
                    class="mt-9 pt-7 overflow-hidden"
                    elevation="1"
                    min-height="calc(100dvh - 116px)"
                    rounded="lg"
                >
                    <slot name="info"></slot>
                </v-sheet>
            </v-responsive>
        </v-sheet>
    </v-navigation-drawer>
</template>

<script>
import { usePageStore } from "@pinia/pageStore";
import { storeToRefs } from "pinia";

export default {
    name: "page-sidenav",

    props: {
        title: String,
    },

    setup() {
        const store = usePageStore();

        const {
            filters,
            hasSelected,
            helpState,
            highlight,
            page,
            params,
            parent,
            sidenavState,
            search,
            trashed,
            theme,
            usetrash,
        } = storeToRefs(store);

        const { getPageDatas, mapResponseData } = store;

        return {
            filters,
            hasSelected,
            helpState,
            highlight,
            page,
            parent,
            params,
            sidenavState,
            search,
            trashed,
            theme,
            usetrash,

            getPageDatas,
            mapResponseData,

            store,
        };
    },

    data: () => ({
        tabSidenav: "filter",
    }),

    methods: {
        applyFilterData: function () {
            this.params.page = 1;
            this.sidenavState = false;

            this.getPageDatas(this.params);
        },
    },
};
</script>

<style scoped>
.v-navigation-drawer--right {
    border-left-width: 0;
}
</style>
