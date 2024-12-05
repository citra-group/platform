<template>
    <v-sheet color="grey-lighten-5" class="border-thin" rounded>
        <v-toolbar density="compact" style="overflow: visible !important">
            <v-toolbar-title
                class="v-toolbar-title__placeholder text-caption text-uppercase"
                style="overflow: visible !important"
                >{{ title }}</v-toolbar-title
            >

            <v-spacer></v-spacer>

            <v-btn density="compact" icon @click="showTable = !showTable">
                <v-icon size="small" v-if="!hideIcon">table_chart</v-icon>
            </v-btn>
        </v-toolbar>

        <div class="position-relative">
            <slot></slot>

            <v-expand-transition>
                <div
                    v-if="showTable"
                    class="transition-fast-in-fast-out bg-white darken-2 v-card--reveal white--text"
                    style="height: 100%"
                >
                    <v-sheet
                        class="position-relative h-100 overflow-y-auto overflow-x-hidden"
                    >
                        <slot name="table"></slot>
                    </v-sheet>
                </div>
            </v-expand-transition>
        </div>
    </v-sheet>
</template>

<script>
export default {
    name: "widget-card",

    props: {
        title: String,
        vertical: Boolean,
        hideIcon: Boolean,
    },

    data: () => ({
        showTable: false,
    }),
};
</script>

<style scoped>
.v-toolbar-title__placeholder {
    overflow: visible !important;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
