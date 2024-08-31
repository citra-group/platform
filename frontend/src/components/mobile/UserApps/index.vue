<template>
    <v-app-bar
        scroll-behavior="hide elevate"
        scroll-threshold="87"
        :color="`${theme}`"
    >
        <v-toolbar-title class="text-body-2 font-weight-bold"
            >SiMASTEN</v-toolbar-title
        >

        <v-spacer></v-spacer>

        <v-btn
            icon
            v-if="impersonated"
            @click="
                $emit('click:impersonateLeave', {
                    mapUserModule: mapUserModule,
                })
            "
        >
            <v-icon>no_accounts</v-icon>
        </v-btn>
    </v-app-bar>

    <v-sheet
        :color="`${theme}`"
        class="mx-auto position-fixed w-100 rounded-b-xl"
        height="256"
    ></v-sheet>

    <v-main>
        <v-sheet class="bg-transparent position-relative px-4 pt-9 pb-4">
            <v-sheet
                class="position-absolute"
                color="transparent"
                width="calc(100% - 32px)"
                style="top: 0; z-index: 1"
            >
                <div class="d-flex justify-center">
                    <form-icon>
                        <v-img
                            :src="
                                auth.avatar ??
                                `/avatars/${auth.gender}-avatar.svg`
                            "
                        ></v-img>
                    </form-icon>
                </div>
            </v-sheet>

            <v-sheet
                class="position-relative pt-7"
                elevation="1"
                min-height="calc(100dvh - 172px)"
                rounded="lg"
                flat
            >
                <v-card-text class="text-center">
                    <div class="text-body-2 font-weight-medium text-uppercase">
                        {{ auth.username }}
                    </div>
                    <div class="text-caption">{{ auth.usermail }}</div>
                </v-card-text>

                <template
                    v-if="modules.personal && modules.personal.length > 0"
                >
                    <v-divider>
                        <v-chip
                            :color="theme"
                            density="comfortable"
                            size="small"
                            variant="flat"
                            >personal</v-chip
                        >
                    </v-divider>

                    <v-card-text>
                        <v-row dense>
                            <v-col
                                cols="3"
                                v-for="(module, index) in modules.personal"
                                :key="index"
                            >
                                <v-card
                                    class="text-center"
                                    rounded="md"
                                    width="100%"
                                    @click="openModule(module)"
                                    flat
                                >
                                    <v-card-text class="pa-2 pb-1 text-center">
                                        <v-avatar
                                            :color="module.color"
                                            style="font-size: 16px"
                                            size="56"
                                            rounded="pill"
                                        >
                                            <v-icon
                                                :color="module.highlight"
                                                :icon="module.icon"
                                                size="large"
                                            ></v-icon>
                                        </v-avatar>
                                    </v-card-text>

                                    <v-card-text
                                        :class="`text-${theme}-darken-1 pa-1`"
                                        style="max-height: 38px"
                                    >
                                        <div
                                            class="text-caption font-weight-medium d-flex align-center justify-center overflow-hidden w-100"
                                            style="
                                                font-size: 10px !important;
                                                line-height: 1.1em;
                                                height: 24px;
                                            "
                                        >
                                            {{ module.name }}
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </template>

                <template
                    v-if="
                        modules.administrator &&
                        modules.administrator.length > 0
                    "
                >
                    <v-divider>
                        <v-chip
                            :color="theme"
                            density="comfortable"
                            size="small"
                            variant="flat"
                            >administrator</v-chip
                        >
                    </v-divider>

                    <v-card-text>
                        <v-row dense>
                            <v-col
                                cols="3"
                                v-for="(module, index) in modules.administrator"
                                :key="index"
                            >
                                <v-card
                                    rounded="md"
                                    width="100%"
                                    @click="openModule(module)"
                                    flat
                                >
                                    <v-card-text class="pa-2 pb-1 text-center">
                                        <v-avatar
                                            rounded="pill"
                                            :color="module.color"
                                            style="font-size: 16px"
                                            size="56"
                                        >
                                            <v-icon
                                                :color="module.highlight"
                                                :icon="module.icon"
                                                size="large"
                                            ></v-icon>
                                        </v-avatar>
                                    </v-card-text>

                                    <v-card-text
                                        :class="`text-${theme}-darken-1 pa-1`"
                                        style="max-height: 38px"
                                    >
                                        <div
                                            class="text-caption font-weight-medium d-flex align-center justify-center overflow-hidden w-100"
                                            style="
                                                font-size: 10px !important;
                                                line-height: 1.1em;
                                                height: 24px;
                                            "
                                        >
                                            {{ module.name }}
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </template>
            </v-sheet>
        </v-sheet>
    </v-main>
</template>

<script>
import { usePageStore } from "@pinia/pageStore";
import { storeToRefs } from "pinia";

export default {
    name: "user-apps",

    props: {
        pageName: {
            type: String,
            default: null,
        },

        pageKey: {
            type: String,
            default: null,
        },
    },

    emits: {
        "click:impersonateLeave": null,
    },

    setup(props) {
        const store = usePageStore();

        store.pageName = props.pageName;
        store.pageKey = props.pageKey;

        const {
            auth,
            highlight,
            impersonated,
            modules,
            navigationState,
            theme,
        } = storeToRefs(store);

        const { getUserModules, mapUserModule, signOut } = store;

        return {
            auth,
            highlight,
            impersonated,
            modules,
            navigationState,
            theme,

            getUserModules,
            mapUserModule,
            signOut,
        };
    },

    created() {
        this.getUserModules();
    },

    methods: {
        openModule: function (module) {
            this.$router.push({ name: module.slug + "-dashboard" });
        },
    },
};
</script>
