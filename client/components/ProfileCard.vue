<template>
    <div class="w-full">
        <div
            class="flex items-center justify-center min-h-[10vh]"
            v-if="loading"
        >
            Loading...
        </div>

        <template v-else>
            <div v-if="!user">
                <div class="text-center font-medium">User not found</div>
            </div>

            <div
                v-if="user"
                :class="{
                    'pb-3': !editingEnabled && !disableEditing,
                }"
            >
                <EditButton
                    class="absolute -bottom-5 -right-5"
                    v-if="isEditable && !editingEnabled"
                    @click="edit"
                />

                <div
                    :class="{
                        'flex items-start justify-center': true,
                        'flex-col items-center': editingEnabled,
                        'gap-4': !editingEnabled,
                    }"
                >
                    <div class="flex flex-col items-center">
                        <input
                            type="file"
                            class="hidden"
                            id="file"
                            @input="fileSelected"
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                        />

                        <label
                            :class="{
                                'flex items-center justify-center rounded-lg w-[100px] aspect-square': true,
                                'rd-bg text-white': !avatar,
                                'cursor-pointer': editingEnabled,
                            }"
                            :for="editingEnabled ? 'file' : 'none'"
                            :style="
                                avatar
                                    ? `background: url(${avatar}) center / cover`
                                    : undefined
                            "
                        >
                            <UserIcon
                                v-show="!avatar && !editingEnabled"
                                class="w-14"
                            />
                            <CameraIcon
                                v-show="editingEnabled && !avatar"
                                class="w-14"
                            />
                        </label>

                        <div
                            :class="{
                                'flex items-center mt-1 -ml-2 font-bold': true,
                            }"
                        >
                            <span
                                :class="{
                                    'text-sm': true,
                                    'bg-slate-200 rounded-l p-1 pr-0':
                                        editingEnabled,
                                }"
                                >@</span
                            >
                            <span
                                @input="username = $event.target.innerText"
                                :class="{
                                    'text-center focus:outline-none text-sm': true,
                                    'bg-slate-200 rounded-r p-1 pl-0':
                                        editingEnabled,
                                }"
                                :contenteditable="editingEnabled"
                                maxlength="20"
                                ref="usernameRef"
                                >{{ username }}</span
                            >
                        </div>

                        <!-- <div class="mt-1" v-if="!editingEnabled">
            <p class="text-sm text-gray-500">
              Joined
              <strong>{{ format(user.createdAt, "MMM y") }}</strong>
            </p>
          </div> -->
                    </div>

                    <div
                        class="flex flex-col gap-1 flex-1"
                        v-if="!editingEnabled"
                    >
                        <div
                            v-for="stat in stats"
                            :key="stat.label"
                            class="min-w-[20vw] flex justify-between items-center gap-4 text-white rd-bg p-2 rounded text-sm"
                        >
                            <p>{{ stat.label }}</p>
                            <p class="font-bold">{{ stat.value }}</p>
                        </div>
                    </div>

                    <ConfirmOrCancel
                        v-if="editingEnabled"
                        @confirm="save"
                        @cancel="cancel"
                        :disabled="saving"
                    >
                        {{ saving ? "Saving..." : "Save changes" }}
                    </ConfirmOrCancel>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { PropType, computed, nextTick, onMounted, ref, toRefs } from "vue";
import { User, useAuthStore } from "../store/authStore";
import { UserIcon, CameraIcon } from "heroicons-vue3/solid";
import EditButton from "../components/EditButton.vue";
import { api } from "../api";
import ConfirmOrCancel from "./ConfirmOrCancel.vue";
import { previewFile } from "../utils/filePreview";
import { useToast } from "../composables/useToast";

const props = defineProps({
    username: String,
    disableEditing: Boolean,
});

const authStore = useAuthStore();

const loading = ref(false);
const editingEnabled = ref(false);
const { username: un } = toRefs(props);
const username = ref("");
const usernameRef = ref();
const user = ref<User>();
const isEditable = computed(() => user.value?.id === authStore.user?.id);

const fetchUser = async () => {
    loading.value = true;

    try {
        const res = await api
            .get(un?.value ? `/users/${un.value}` : "/me", {
                params: {
                    username: un?.value,
                },
            })
            .then((res) => res.data);
        user.value = res.user;
        username.value = res.user.username;
    } catch (err) {
        console.error(err);
    }

    loading.value = false;
};

const stats = computed(() =>
    user.value
        ? [
              { label: "Tricks", value: user.value.sirs },
              { label: "Games", value: user.value.games_played },
              { label: "Wins", value: user.value.games_won },
              { label: "Courts", value: user.value.courts },
              { label: "Goon Courts", value: user.value.goon_courts },
          ]
        : []
);

const edit = () => {
    editingEnabled.value = true;
    nextTick(() => usernameRef.value.focus());
};

const cancel = () => {
    editingEnabled.value = false;
    username.value = user.value?.username || "";
    previewSrc.value = null;
};

const toast = useToast();

const saving = ref(false);
const save = async () => {
    if (
        !username.value ||
        username.value.length > 20 ||
        username.value.length < 3
    ) {
        toast.error("Invalid username");
        return;
    }

    saving.value = true;
    try {
        const fd = new FormData();
        fd.append("username", username.value);
        if (selectedFile.value) {
            fd.append("avatar", selectedFile.value);
        }

        await api.post("/me", fd, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        if (user.value) {
            authStore.setUser({ ...user.value, username: username.value });
        }

        editingEnabled.value = false;
    } catch (err) {
        console.error(err);
    }
    saving.value = false;
};

onMounted(fetchUser);

const previewSrc = ref<string | null>(null);
const selectedFile = ref<File | null>(null);

const avatar = computed(() => previewSrc.value || user.value?.avatar);
const fileSelected = async (e: any) => {
    const files = e?.target?.files || [];
    if (!files.length) {
        return (previewSrc.value = null);
    }
    const file = files[0];
    if (file.size > 5 * 1024 * 1024) {
        toast.error("Image should be smaller than 5MB");
        return;
    }
    if (!file.type.startsWith("image")) {
        toast.error("Please select an image");
        return;
    }

    selectedFile.value = file;
    previewSrc.value = await previewFile(file);
};
</script>
