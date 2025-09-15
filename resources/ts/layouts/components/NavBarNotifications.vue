<script lang="ts" setup>
import Notifications from "@core/components/Notifications.vue";
import type { Notification } from "@layouts/types";

import axios from "axios";

// Images
import avatar3 from "@images/avatars/avatar-3.png";
import avatar4 from "@images/avatars/avatar-4.png";
import avatar5 from "@images/avatars/avatar-5.png";
import paypal from "@images/svg/paypal.svg";
import logo from "@images/logo.png";
import { environment } from "@/environments/environment";
import { color } from "@/views/demos/components/badge/demoCodeBadge";
import { dataToEsm } from "@rollup/pluginutils";

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const clientNotif = ref();
const count = ref();
const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");

const notif = () => {
  axios
    .get(environment.API_URL + `client/client/notification/${client_id[0]?.id}`,{
      params: {
        token: token
      }
    })
    .then((response) => {
      if (response.data.success) {
        clientNotif.value = response.data.clientNotif;
        count.value = response.data.count;
        client_notifications = clientNotif.value.map(
          (item: {
            id: any;
            time: any;
            payment_id: any;
            request_id: any;
            request: {
              request_status: any;
              release_status: any;
              editor_uploaded_file: any;
            };
            published: string;
            payment: { payment_status: any };
            is_read: any;
          }) => {
            return {
              id: item.id,
              time: item.time,
              img: logo,
              payment_id: item.payment_id,
              request_id: item.request_id,
              req_status: item.request?.request_status,
              publish: item.request?.release_status,
              done: item.published,
              text: item.request?.editor_uploaded_file,
              title: item.request
                ? item.published == "1"
                  ? `Your Request is ${item.request?.request_status}`
                  : `The Journal is Published`
                : `Your Payment is ${item.payment?.payment_status}`,
              subtitle: item.is_read,
            };
          }
        );
      }
    });
  // }
};
let client_notifications: Notification[] = [];

const number = ref();
const adminNotificationData = ref();
const validator = ref(false)
const adminNotif = () => {
  axios.get(environment.API_URL + "auth/notification",{
    params: {
      token:token
    }
  }).then((response) => {
    if (response.data.success) {
      adminNotificationData.value = response.data.adminNotif;
      number.value = response.data.count;

      // Directly assign the new notifications array
      admin_notifications = adminNotificationData.value.map(
        (data: {
          id: any;
          time: any;
          payment: {
            client: {
              image: any;
              firstname: any;
              middlename: string;
              lastname: any;
            };
          };
          payment_id: any;
          request_id: any;
          request: { request_status: any; release_status: any };
          is_read: any;
        }) => {
          return {
            id: data.id,
            time: data.time,
            img:
              data.editor_id !== null
                ? data.editor?.image
                : data.payment?.client.image,
            payment_id: data.payment_id,
            request_id: data.request_id,
            req_status: data.request?.editor_status,
            publish: data.request?.release_status,
            done:data.published,
            journal: data.request?.editor_uploaded_file,
            text:
              data.editor_id !== null
                ? `${data.editor?.lastname} ${data.editor?.firstname} ${
                    data.editor?.middlename || ""
                  }`
                : `${data.payment?.client.firstname} ${
                    data.payment?.client.middlename
                      ? data.payment?.client.middlename + " "
                      : ""
                  }${data.payment?.client.lastname}`,
            title: data.request
              ? data.editor_id !== null &&
                data.request?.editor_status === "Approved" &&
                data.published == 1
                ? `Approved By Editor`
                : data.editor_id !== null &&
                  data.request?.editor_status === "Approved" &&
                  data.published == 0
                ? "Editor Published a Journal"
                : data.editor_id !== null &&
                  data.request?.editor_status === "Rejected" &&
                  data.published == 1
                ? "Rejected by Editor"
                : "Journal Request"
              : "Payment Request",
            subtitle: data.is_read,
          };
        }
      );
    }
  });
};
let admin_notifications: Notification[] = [];
const len = ref()
const editorDataNotif = ref()
const editor_id = JSON.parse(localStorage.getItem("editorData") || "{}");

const editorNotif = () => {
  axios.get(environment.API_URL + "editor/editor/notification/" + editor_id[0]?.id,{
    params: {
      token:token
    }
  }).then(response => {
    if(response.data.success){
       editorDataNotif.value = response.data.editor
       len.value = response.data.count
      //  console.log(editorDataNotif.value)
       editor_notification = editorDataNotif.value.map((row: { id: any; time: any; editor_id: null; editor: { image: any; }; payment: { client: { image: any; }; }; payment_id: any; request_id: any; request: { request_status: any; }; }) => {
        return {
          id: row.id,
          time: row.time,
          img:logo,
          payment_id: row.payment_id,
          request_id: row.request_id,
          req_status: row.request?.request_status,
          subtitle: row.is_read,
          title: row.request?.uploaded_file
        }
       })   
    }
  })
}


let editor_notification: Notification[] = []
onMounted(() => {
  notif();
  adminNotif();
  editorNotif();

  setInterval(() => {
    notif();
    adminNotif();
    editorNotif();
  }, 60000);
});
const handleData = () => {
  notif();
};
const handleDatas = () => {
  adminNotif();
};
const handleFresh = () => {
  editorNotif()
}

setTimeout(() =>{
  editorNotif();
  adminNotif
});
</script>

<template>
  <Notifications
    @data-admin="handleDatas"
    :notifications="client_notifications"
    :data="admin_notifications"
    :item="editor_notification"
    :number="number"
    :row="count"
    :seq="len"
    @data="handleData"
    @data-editor="handleFresh"
  />
</template>
