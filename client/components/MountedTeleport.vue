<template>
    <Teleport :to="to" :disabled="disabled" v-if="isMounted"
        ><slot></slot
    ></Teleport>
</template>

<script>
export default {
    name: "MountedTeleport",
    props: ["to", "disabled"],
    data() {
        return { isMounted: false };
    },
    mounted() {
        this.isMounted = true;
    },
};
</script>
