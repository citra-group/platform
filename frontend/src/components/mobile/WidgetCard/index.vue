<template>
    <v-sheet color="grey-lighten-5" class="border-thin" rounded>
        <v-toolbar density="compact">
            <v-toolbar-title class="text-caption text-uppercase">{{
                title
            }}</v-toolbar-title>

            <v-spacer></v-spacer>

            <v-btn density="compact" icon @click="showTable = !showTable">
                <v-icon size="small">table_chart</v-icon>
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
    },

    data: () => ({
        showTable: false,
    }),
};
</script>
