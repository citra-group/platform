<template>
    <v-app-bar
        :color="theme"
        scroll-behavior="hide elevate"
        scroll-threshold="87"
    >
        <v-btn icon v-if="parentName">
            <v-icon>arrow_back</v-icon>
        </v-btn>

        <v-toolbar-title class="text-body-2 font-weight-bold text-uppercase">{{
            module.name
        }}</v-toolbar-title>

        <v-spacer></v-spacer>
    </v-app-bar>

    <v-sheet
        :color="`${theme}`"
        class="mx-auto position-fixed w-100 rounded-b-xl"
        height="256"
    ></v-sheet>

    <v-main>
        <v-card-text>
            <slot
                :combos="combos"
                :highlight="highlight"
                :record="record"
                :store="store"
                :theme="theme"
            ></slot>
        </v-card-text>
    </v-main>
</template>

<script>
import { usePageStore } from "@pinia/pageStore";
import { storeToRefs } from "pinia";

export default {
    name: "page-blank",

    props: {
        maxWidth: {
            type: [String, Number],
            default: "500",
        },

        navbackTo: String,

        pagePath: {
            type: String,
            default: null,
        },

        pageName: {
            type: String,
            default: null,
        },

        pageKey: {
            type: String,
            default: null,
        },

        parentName: {
            type: String,
            default: null,
        },

        parentKey: {
            type: String,
            default: null,
        },

        sidenavTitle: {
            type: String,
            default: "Utility",
        },

        title: String,

        showSidenav: {
            type: Boolean,
            default: false,
        },
    },

    setup(props) {
        const store = usePageStore();

        store.pageKey = props.pageKey;
        store.pageName = props.pageName;
        store.pagePath = props.pagePath;

        const {
            combos,
            highlight,
            module,
            navigationState,
            page,
            sidenavState,
            railMode,
            record,
            theme,
        } = storeToRefs(store);

        const { getPageData } = store;

        return {
            combos,
            highlight,
            module,
            navigationState,
            page,
            sidenavState,
            railMode,
            record,
            theme,

            getPageData,
            store,
        };
    },

    created() {
        this.getPageData();
    },
};
</script>
