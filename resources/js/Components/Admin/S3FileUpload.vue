<template>
  <div class="dropzon_wrap">
    <template v-if="origin">
      <template v-if="type == 'video'">
        <three-video-player 
          v-if="origin" 
          :video="origin"
        />
      </template>
      <template v-if="type == 'image'">
        <v-img
          :width="300"
          aspect-ratio="16/9"
          cover
          src="origin"
        ></v-img>
      </template>
    </template>
    <template v-else>
      <div ref="dropzone" class="dropzone needsclick">
        <div class="dz-message needsclick">{{ label }}</div>
      </div>
      <v-input :error-messages="error">
      </v-input>
    </template>
  </div>
</template>

<script>
import Dropzone from "dropzone";

export default {
  props: [ 'type', 'label', 'error', 'origin' ],
  data() {
    return {
      upload_url: "/admin/media/upload",
      method: "post",
      auto_process_queue: false,
      accepted_files: null,
      file_extension: null,
      file_path: null,
      file_name: null,
      dropzone: null
    }
  },
  mounted() {
    this.init_data();
    this.init_dropzone();
  },
  methods: {
    init_data() {
      if(this.type == 'video') {
        this.accepted_files = "video/*"
        this.method = "put";
      } else if(this.type == 'gpx') {
        this.accepted_files = ".gpx"
        this.auto_process_queue = true
      } else { // image
        this.accepted_files = "image/*"
      }
    },
    async init_presigned_upload(params) {
      const self = this
      self.dropzone.options.clickable = false;
      try {
        const { data } = await axios.post(`/admin/media/new_presigned_url`, params);
        if(data.success) {
          self.file_path = data.file_path
          self.file_name = data.file_name
          self.presigned_url = data.file_name
          self.dropzone.options.url = data.presigned_url
          console.log('files', self.dropzone.files)
          if(self.dropzone.files.length > 0) {
            self.$emit('status', true)
            self.dropzone.processQueue();
          }
        } else {
          self.show_toast()
        }
      } catch(e) {
        console.log(e);
        self.show_toast()
      } finally {
        self.dropzone.options.clickable = true;
      }
    },
    init_file_upload() {
      const self = this
      if(self.dropzone.files.length > 0) {
        self.dropzone.options.url = self.upload_url
        setTimeout(function() {
          self.$emit('status', true)
          self.dropzone.processQueue();
        }, 500)
      }
    },
    init_dropzone() {
      const self = this
      this.dropzone = new Dropzone(this.$refs.dropzone, {
        url: self.upload_url,
        method: self.method,
        thumbnailHeight: 120,
        thumbnailWidth: 120,
        maxFilesize: 1024 * 1024 * 1024 * 10, // 10Gbyte
        maxFiles: 1,
        // acceptedFiles: self.accepted_files,
        addRemoveLinks: true,
        autoProcessQueue: false,
        dictRemoveFile: "削除",
        dictCancelUpload: "キャンセル",
        // previewTemplate: previewTemplate,
        headers: {
            'X-CSRF-TOKEN': self.$page.props.csrf_token
        }
      })
      
      this.dropzone.on("addedfile", function(file) {
        console.log('file', file);
        if (this.files.length > 1) {
          this.removeFile(this.files[0]);
        }
        if(self.type == 'video') {
          const extension = file.name.split('.').pop().toLowerCase()
          self.init_presigned_upload({
            extension: extension,
            type: file.type
          })
        }
         else {
          self.init_file_upload()
        }
      });

      this.dropzone.on("removedFile", function(file) {
        if (this.files.length == 0) {
          self.$emit("remove")
        }
      });

      this.dropzone.on("success", function (file, response) {
        self.$emit('status', false)
        self.$emit('success', {
          file_path: self.file_path,
          file_name: self.file_name,
        })
      });

      this.dropzone.on("error", function (file, errorMessage) {
        self.$emit('status', false)
        self.show_toast()
      });


      this.dropzone.on("sending", function (file, xhr, formData) {
        xhr.setRequestHeader("Content-Type", file.type);
      });

      this.dropzone.on("uploadprogress", function(file, progress, bytesSent) {
        if (file.previewElement) {
          const progress_text = progress.toFixed(2);
          var progressElement = file.previewElement.querySelector(".dz-progress");
          progressElement.innerHTML = `<span class="dz-status" >${progress_text}%</span><span class="dz-upload" data-dz-uploadprogress style="width: ${progress}%;"></span>`
        }
      })
    }
  }
};
</script>