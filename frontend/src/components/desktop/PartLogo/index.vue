<template>
    <div class="d-flex align-center px-1" style="height: 72px">
        <img
            class="px-1"
            src="/images/citrapps.svg"
            style="margin-left: 0px; height: 48px; width: auto"
        />
        <!-- <img
            class="px-1"
            :src="auth.avatar ?? `/avatars/${auth.gender}-avatar.svg`"
            style="margin-left: 0px; height: 48px; width: auto"
        /> -->

        <div class="d-flex flex-column pl-4">
            <div
                class="text-h5 font-brand"
                style="color: rgba(255, 255, 255, 0.81)"
            >
                CITR<span style="color: rgba(255, 255, 255, 0.36)">APPS</span>
            </div>

            <div class="text-caption text-white" style="line-height: 1">
                {{ auth.username }}
            </div>

            <!-- <div class="text-caption text-white" style="line-height: 1">
                version 3.0.0
            </div> -->
        </div>
    </div>
</template>

<script>
import { usePageStore } from "@pinia/pageStore";
import { storeToRefs } from "pinia";
export default {
    name: "part-logo",
    props: {
        textLeft: String,
        textRight: String,
        maxWidth: {
            type: [String, Number],
            default: "100%",
        },
        userAvatar: Boolean,
    },

    setup() {
        const store = usePageStore();

        const { auth, theme } = storeToRefs(store);

        return {
            auth,
            theme,
        };
    },
};
</script>
