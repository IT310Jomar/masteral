<script lang="ts" setup>
import { environment } from "@/environments/environment";
import { avatarText } from "@core/utils/formatters";
import type { Notification } from "@layouts/types";
// import type { AdminNotification } from "@layouts/types";
import axios from "axios";
import { useRouter } from "vue-router";
import type { Anchor } from "vuetify/lib/components";

interface Props {
  notifications: Notification[];
  data: Notification[];
  item: Notification[];
  badgeProps?: unknown;
  location?: Anchor;
  row: any;
  number: any;
  seq: any;
}

const router = useRouter();
const props = withDefaults(defineProps<Props>(), {
  location: "bottom end",
  badgeProps: undefined,
});

// const emit = defineEmits<{
//   (e: "click:readAllNotifications"): void;
// }>();
const emit = defineEmits(["data", "dataAdmin", "dataEditor"]);

function formatDate(time: string | number | Date) {
  const now = new Date();
  const notificationTime = new Date(time);
  const timeDiff = now - notificationTime;
  const seconds = Math.floor(timeDiff / 1000);
  const minutes = Math.floor(seconds / 60);
  const hours = Math.floor(minutes / 60);
  const days = Math.floor(hours / 24);

  if (days > 1) {
    return `${days} d ago`;
  } else if (days === 1) {
    return "Yesterday";
  } else if (hours > 0) {
    return `${hours} h ago`;
  } else if (minutes > 0) {
    return `${minutes} m ago`;
  } else if (seconds > 0) {
    return `${seconds} s ago`;
  } else {
    return "Just now";
  }
}
const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");
const markAsReadAll = () => {
  axios
    .put(
      environment.API_URL + "client/client/mark-as-read-all/" + client_id[0]?.id
    )
    .then((response) => {
      if (response.data.success) {
        emit("data", response.data);
      }
    });
};
const markAsReadAllAdmin = () => {
  axios.put(environment.API_URL + "auth/mark-as-read-all").then((response) => {
    if (response.data.success) {
      emit("dataAdmin", response.data);
    }
  });
};

//client
const markRead = (
  id: any,
  is_read: any,
  payment_id: any,
  request_id: any,
  publish: any,
  req_status: any,
  done: any
) => {
  if (is_read != 0) {
    // console.log(props.notifications, payment_id, request_id);

    axios
      .put(environment.API_URL + "client/client/mark-as-read/" + id)
      .then((response) => {
        emit("data", response.data);

        if (payment_id !== null && request_id == null && done == 1) {
          router.push("/client/transaction/payment");
        } else if (payment_id !== null && request_id !== null && done == 1) {
          if (req_status == "Approved") {
            router.push("/client/my-request/approved");
          } else {
            router.push("/client/my-request/rejected");
          }
        } else if (
          payment_id !== null &&
          request_id !== null &&
          publish == 0 &&
          done == 0
        ) {
          router.push("/client/publish_journal/download_docs");
        }
      });
  } else {
    if (payment_id !== null && request_id == null && done == 1) {
      router.push("/client/transaction/payment");
    } else if (payment_id !== null && request_id !== null && done == 1) {
      if (req_status == "Approved") {
        router.push("/client/my-request/approved");
      } else {
        router.push("/client/my-request/rejected");
      }
    } else if (
      payment_id !== null &&
      request_id !== null &&
      publish == 0 &&
      done == 0
    ) {
      router.push("/client/publish_journal/download_docs");
    }
  }
};

//admin
const validator = ref(false);
const mark = (
  id: any,
  is_read: any,
  title: any
) => {
  // console.log(title);
  if (is_read != 0) {
    axios
      .put(environment.API_URL + "auth/admin/mark-as-read/" + id)
      .then((response) => {
        emit("dataAdmin", response.data);
        if (title == "Payment Request") {
          router.push("/admin/request/paid_accounts");
        } else if (title == "Journal Request") {
          router.push("/admin/request/uploaded_journal");
        } else if (title == "Approved By Editor") {
          router.push("/admin/approved_editor/approved_for_editing");
        } else if (title == "Editor Published a Journal") {
          router.push("/admin/published/publishedjournal");
        }
      });
  } else {
    if (title == "Payment Request") {
      router.push("/admin/request/paid_accounts");
    } else if (title == "Journal Request") {
      router.push("/admin/request/uploaded_journal");
    } else if (title == "Approved By Editor") {
      router.push("/admin/approved_editor/approved_for_editing");
    } else if (title == "Editor Published a Journal") {
      router.push("/admin/published/publishedjournal");
    }
  }
};
const userRole = JSON.parse(localStorage.getItem("userRole") || "{}");
const editor_id = JSON.parse(localStorage.getItem("editorData") || "{}");
const markAsReadAllEditor = () => {
  axios
    .put(
      environment.API_URL + "editor/editor/mark-as-read-all/" + editor_id[0]?.id
    )
    .then((response) => {
      if (response.data.success) {
        emit("dataEditor", response.data);
      }
    });
};
const markEditor = (id: any) => {
  axios
    .put(environment.API_URL + "editor/editor/mark-as-read/" + id)
    .then((response) => {
      if (response.data.success) {
        emit("dataEditor", response.data);
        router.push("/editor/approved_documents");
      }
    });
};
</script>

<template>
  <VBadge
    v-if="userRole == 'Client'"
    :model-value="!!props.badgeProps"
    v-bind="props.badgeProps"
  >
    <VBtn icon variant="text" color="default" size="small">
      <VBadge v-if="$props.row > 0" color="error" :content="$props.row">
        <VIcon icon="tabler-bell" size="24" />
      </VBadge>
      <VIcon v-else icon="tabler-bell" size="24" />
      <VMenu
        activator="parent"
        width="380px"
        :location="props.location"
        offset="14px"
      >
        <VList
          class="py-0 d-flex flex-column flex-grow-1 overflow-auto"
          max-height="400"
        >
          <!-- 👉 Header -->
          <VListItem
            title="Notifications"
            class="notification-section"
            height="48px"
          >
            <template #append>
              <VChip v-if="props.row" color="error" size="small">
                {{ props.row }} Unread
              </VChip>
            </template>
          </VListItem>

          <VDivider />

          <!-- 👉 Notifications list -->
          <template
            v-for="notification in props.notifications"
            :key="notification.title"
          >
            <VListItem
              :title="notification.title"
              @click="
                markRead(
                  notification.id,
                  notification.subtitle,
                  notification.payment_id,
                  notification.request_id,
                  notification.publish,
                  notification.req_status,
                  notification.done
                )
              "
              link
              lines="three"
              min-height="66px"
            >
              <template v-slot:subtitle v-if="notification.done == '0'">
                <small class="text-success">{{ notification.text }}</small
                ><br />
                <span
                  :class="{
                    'unread-subtitle': notification.subtitle == '1',
                    'read-subtitle': notification.subtitle == '0',
                  }"
                >
                  {{ notification.subtitle == "0" ? "Read" : "Unread" }}
                </span>
              </template>
              <template v-slot:subtitle v-else>
                <span
                  :class="{
                    'unread-subtitle': notification.subtitle == '1',
                    'read-subtitle': notification.subtitle == '0',
                  }"
                >
                  {{ notification.subtitle == "0" ? "Read" : "Unread" }}
                </span>
              </template>
              <!-- Slot: Prepend -->
              <!-- Handles Avatar: Image, Icon, Text -->
              <template #prepend>
                <VListItemAction start>
                  <VAvatar
                    :color="notification.color || 'primary'"
                    :image="notification.img || undefined"
                    :icon="notification.icon || undefined"
                    size="40"
                    variant="tonal"
                  >
                    <span v-if="notification.text">{{
                      avatarText(notification.text)
                    }}</span>
                  </VAvatar>
                </VListItemAction>
              </template>
              <!-- Slot: Append -->
              <template #append>
                <small class="whitespace-no-wrap text-medium-emphasis">{{
                  formatDate(notification.time)
                }}</small>
              </template>
            </VListItem>
            <VDivider />
          </template>

          <!-- 👉 Footer -->
        </VList>
        <VRow>
          <VCol>
            <VBtn block @click.prevent="markAsReadAll" style="width: 20px">
              READ ALL NOTIFICATIONS
            </VBtn>
          </VCol>
        </VRow>
      </VMenu>
    </VBtn>
  </VBadge>
  <VBadge
    v-if="userRole == 'Admin' || userRole == 'God Mode'"
    :model-value="!!props.badgeProps"
    v-bind="props.badgeProps"
  >
    <VBtn icon variant="text" color="default" size="small">
      <VBadge v-if="$props.number > 0" color="error" :content="$props.number">
        <VIcon icon="tabler-bell" size="24" />
      </VBadge>
      <VIcon v-else icon="tabler-bell" size="24" />
      <VMenu
        activator="parent"
        width="380px"
        :location="props.location"
        offset="14px"
      >
        <VList
          class="py-0 d-flex flex-column flex-grow-1 overflow-auto"
          max-height="400"
        >
          <!-- 👉 Header -->
          <VListItem
            title="Notifications"
            class="notification-section"
            height="48px"
          >
            <template #append>
              <VChip color="error" size="small">
                {{ props.number }} Unread
              </VChip>
            </template>
          </VListItem>

          <VDivider />

          <!-- 👉 Notifications list -->
          <template
            v-for="notification in props.data"
            :key="notification.title"
          >
            <VListItem
              @click="
                mark(
                  notification.id,
                  notification.subtitle,
                  notification.title
                )
              "
              :title="notification.title"
              link
              lines="two"
              min-height="66px"
            >
              <template v-slot:subtitle>
                <small class="text-success">{{ notification.text }}</small
                ><br />
                <!-- <small v-if="notification.done == 0" class="text-blue">{{ notification.journal }}</small><br/> -->
                <span
                  :class="{
                    'unread-subtitle': notification.subtitle == '1',
                    'read-subtitle': notification.subtitle == '0',
                  }"
                >
                  {{ notification.subtitle == "0" ? "Read" : "Unread" }}
                </span>
              </template>
              <!-- Slot: Prepend -->
              <!-- Handles Avatar: Image, Icon, Text -->
              <template #prepend>
                <VListItemAction start>
                  <VAvatar
                    :color="notification.color || 'primary'"
                    :image="
                      '/storage/profile_image/' + notification.img || undefined
                    "
                    :icon="notification.icon || undefined"
                    size="40"
                    variant="tonal"
                  >
                    <span v-if="notification.text">{{
                      avatarText("hello")
                    }}</span>
                  </VAvatar>
                </VListItemAction>
              </template>

              <!-- Slot: Append -->
              <template #append>
                <small class="whitespace-no-wrap text-medium-emphasis">{{
                  formatDate(notification.time)
                }}</small>
              </template>
            </VListItem>
            <VDivider />
          </template>

          <!-- 👉 Footer -->
        </VList>
        <VRow>
          <VCol>
            <VBtn block @click.prevent="markAsReadAllAdmin" style="width: 20px">
              READ ALL NOTIFICATIONS
            </VBtn>
          </VCol>
        </VRow>
      </VMenu>
    </VBtn>
  </VBadge>
  <VBadge
    v-if="userRole == 'Editor'"
    :model-value="!!props.badgeProps"
    v-bind="props.badgeProps"
  >
    <VBtn icon variant="text" color="default" size="small">
      <VBadge v-if="$props.seq > 0" color="error" :content="$props.seq">
        <VIcon icon="tabler-bell" size="24" />
      </VBadge>
      <VIcon v-else icon="tabler-bell" size="24" />
      <VMenu
        activator="parent"
        width="380px"
        :location="props.location"
        offset="14px"
      >
        <VList
          class="py-0 d-flex flex-column flex-grow-1 overflow-auto"
          max-height="400"
        >
          <!-- 👉 Header -->
          <VListItem
            title="Notifications"
            class="notification-section"
            height="48px"
          >
            <template #append>
              <VChip v-if="props.row" color="error" size="small">
                {{ props.seq }} Unread
              </VChip>
            </template>
          </VListItem>

          <VDivider />

          <!-- 👉 Notifications list -->
          <template
            v-for="notification in props.item"
            :key="notification.title"
          >
            <VListItem
              :title="notification.title"
              link
              lines="two"
              min-height="66px"
              @click.prevent="markEditor(notification.id)"
            >
              <template v-slot:subtitle>
                <span
                  :class="{
                    'unread-subtitle': notification.subtitle == '1',
                    'read-subtitle': notification.subtitle == '0',
                  }"
                >
                  {{ notification.subtitle == "0" ? "Read" : "Unread" }}
                </span>
              </template>
              <!-- Slot: Prepend -->
              <!-- Handles Avatar: Image, Icon, Text -->
              <template #prepend>
                <VListItemAction start>
                  <VAvatar
                    :color="notification.color || 'primary'"
                    :image="notification.img || undefined"
                    :icon="notification.icon || undefined"
                    size="40"
                    variant="tonal"
                  >
                    <span v-if="notification.text">{{
                      avatarText(notification.text)
                    }}</span>
                  </VAvatar>
                </VListItemAction>
              </template>
              <!-- Slot: Append -->
              <template #append>
                <small class="whitespace-no-wrap text-medium-emphasis">{{
                  formatDate(notification.time)
                }}</small>
              </template>
            </VListItem>
            <VDivider />
          </template>

          <!-- 👉 Footer -->
        </VList>
        <VRow>
          <VCol>
            <VBtn
              block
              style="width: 20px"
              @click.prevent="markAsReadAllEditor"
            >
              READ ALL NOTIFICATIONS
            </VBtn>
          </VCol>
        </VRow>
      </VMenu>
    </VBtn>
  </VBadge>
</template>

<style lang="scss">
.notification-section {
  padding: 14px !important;
}

.unread-subtitle {
  color: #ff9f43; /* Change to the desired color for Unread */
}

.read-subtitle {
  color: #00cfe8; /* Change to the desired color for Unread */
}
</style>
