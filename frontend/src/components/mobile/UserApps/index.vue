<template>
    <v-responsive class="h-100 w-100 overflow-x-hidden overflow-y-auto">
        <v-toolbar :color="`${theme}`">
            <v-toolbar-title class="text-body-2 font-weight-bold"
                >SiMASTEN</v-toolbar-title
            >

            <v-spacer></v-spacer>
        </v-toolbar>

        <v-sheet
            :color="`${theme}`"
            class="mx-auto position-absolute w-100 rounded-b-xl"
            height="192"
        ></v-sheet>

        <v-sheet
            :height="
                navigationState ? `calc(100vh - 120px)` : `calc(100vh - 64px)`
            "
            class="bg-transparent overflow-x-hidden overflow-y-auto px-4 pb-4 position-relative"
        >
            <div
                :class="`text-${theme}`"
                class="d-flex justify-center position-relative w-100"
                style="z-index: 1"
            >
                <div class="circle position-absolute mt-1">
                    <v-avatar size="56">
                        <v-img
                            :src="
                                auth.avatar ??
                                `/avatars/${auth.gender}-avatar.svg`
                            "
                        ></v-img>
                    </v-avatar>
                </div>
            </div>

            <v-card
                class="mt-9 pt-9"
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
                        <v-row justify="center" dense>
                            <v-col
                                cols="3"
                                v-for="(module, index) in modules.personal"
                                :key="index"
                            >
                                <v-card
                                    :border="`border-${theme} thin`"
                                    :color="`${theme}-lighten-5`"
                                    class="text-center"
                                    rounded="md"
                                    width="100%"
                                    @click="openModule(module)"
                                    flat
                                >
                                    <v-card-text class="pa-3 pb-2">
                                        <v-avatar
                                            :color="theme"
                                            style="font-size: 16px"
                                        >
                                            <v-icon
                                                :color="highlight"
                                                :icon="module.icon"
                                                size="large"
                                            ></v-icon>
                                        </v-avatar>
                                    </v-card-text>

                                    <v-card-text
                                        :class="`text-${theme}-darken-1`"
                                        class="pa-1 pb-2"
                                        style="max-height: 38px"
                                    >
                                        <div
                                            class="text-caption d-flex align-center justify-center overflow-hidden w-100 h-100"
                                            style="
                                                font-size: 72% !important;
                                                line-height: 1.25em;
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
                        <v-row justify="center" dense>
                            <v-col
                                cols="3"
                                v-for="(module, index) in modules.administrator"
                                :key="index"
                            >
                                <v-card
                                    :border="`border-${theme} thin`"
                                    :color="`${theme}-lighten-5`"
                                    class="text-center"
                                    rounded="md"
                                    width="100%"
                                    @click="openModule(module)"
                                    flat
                                >
                                    <v-card-text class="pa-3 pb-2">
                                        <v-avatar
                                            :color="theme"
                                            style="font-size: 16px"
                                        >
                                            <v-icon
                                                :color="highlight"
                                                :icon="module.icon"
                                                size="large"
                                            ></v-icon>
                                        </v-avatar>
                                    </v-card-text>

                                    <v-card-text
                                        :class="`text-${theme}-darken-1`"
                                        class="pa-1 pb-2"
                                        style="max-height: 38px"
                                    >
                                        <div
                                            class="text-caption d-flex align-center justify-center overflow-hidden w-100 h-100"
                                            style="
                                                font-size: 72% !important;
                                                line-height: 1.25em;
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
            </v-card>
            <slot></slot>
        </v-sheet>
    </v-responsive>
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

    setup(props) {
        const store = usePageStore();

        store.pageName = props.pageName;
        store.pageKey = props.pageKey;

        const { auth, highlight, modules, navigationState, theme } =
            storeToRefs(store);

        const { getUserModules, signOut } = store;

        return {
            auth,
            highlight,
            modules,
            navigationState,
            theme,

            getUserModules,
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
