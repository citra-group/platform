<template>
    <v-layout :class="`bg-${theme}-lighten-4`">
        <router-view key="pagebase" />

        <v-overlay
            :model-value="overlay"
            class="align-center justify-center"
            scroll-strategy="block"
            persistent
        >
            <v-progress-circular
                :color="theme"
                :width="3"
                size="64"
                indeterminate
            >
                <template v-slot:default><small>loading</small></template>
            </v-progress-circular>
        </v-overlay>

        <v-snackbar
            :color="snackbar.color"
            :timeout="1500"
            v-model="snackbar.state"
            multi-line
        >
            {{ snackbar.text }}

            <template v-slot:actions>
                <v-btn
                    color="white"
                    variant="text"
                    @click="snackbar.state = false"
                >
                    Close
                </v-btn>
            </template>
        </v-snackbar>

        <v-bottom-navigation
            :active="navigationState"
            :base-color="`${theme}-lighten-2`"
            :color="`${theme}-darken-1`"
        >
            <v-btn
                v-for="(item, index) in sideMenus"
                :key="index"
                style="width: 25%"
                @click="openPage(item)"
            >
                <v-icon>{{ item.icon }}</v-icon>

                <div style="margin-top: 2px; font-size: 88%">
                    {{ item.name }}
                </div>
            </v-btn>
        </v-bottom-navigation>
    </v-layout>
</template>

<script>
import { usePageStore } from "@pinia/pageStore";
import { storeToRefs } from "pinia";

export default {
    name: "page-base",

    props: {
        moduleName: {
            type: String,
            default: null,
        },

        moduleKey: {
            type: String,
            default: null,
        },

        moduleType: {
            type: String,
            default: null,
        },
    },

    setup(props) {
        const store = usePageStore();

        store.moduleName = props.moduleName;
        store.moduleKey = props.moduleKey;
        store.moduleType = props.moduleType;

        const {
            auth,
            dockMenus,
            navigationState,
            overlay,
            railMode,
            sideMenus,
            snackbar,
            theme,
        } = storeToRefs(store);

        const { clearFilters, initModule } = store;

        return {
            auth,
            dockMenus,
            navigationState,
            overlay,
            railMode,
            sideMenus,
            snackbar,
            theme,

            clearFilters,
            initModule,
        };
    },

    created() {
        this.initModule({ mobile: true });
    },

    methods: {
        openPage: function (page) {
            this.clearFilters();

            this.$router.push({ name: page.slug });
        },
    },
};
</script>
