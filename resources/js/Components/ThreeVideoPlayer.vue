<template>
  <div ref="vp_wrap" class="vp_wrap">
    <div class="vp_controls">
      <v-row justify="center">
        <v-col cols="auto">
          <v-btn icon="mdi-rewind" @click="onRewind"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn v-if="isPlaying" icon="mdi-stop" @click="onPause"></v-btn>
          <v-btn v-if="!isPlaying" icon="mdi-play" @click="onPlay"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn icon="mdi-fast-forward" @click="onFast"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn icon="mdi-record" @click="onCapture"></v-btn>
        </v-col>
      </v-row>
      <div class="vp_progress mt-2">
        <v-progress-linear v-model="progress" @click="onSeekTo" :height="10"></v-progress-linear>
      </div>
    </div>
  </div>
</template>

<script>
import { Viewer, VideoPanorama } from 'panolens';

export default {
  props: ['url', 'id', 'time', 'nearsetLatLng'],
  data() {
    return {
      viewer: null,
      panorama: null,
      isPlaying: false,
      progress: 0,
      disabled: false,
      step: 2,
      is_capturing: false
    };
  },
  mounted() {
    this.initVideoPlayer();
  },
  methods: {
    async initVideoPlayer() {
      const self = this
      // Create a viewer for the panorama
      self.viewer = new Viewer({
        container: self.$refs.vp_wrap,
        controlBar: false
      });

      // Create a VideoPanorama with your 360-degree video
      self.panorama = new VideoPanorama(self.url, {
        autoplay: false, // Disable auto-play for custom control handling
      });

      // Add the VideoPanorama to the viewer
      self.viewer.add(self.panorama);
      self.viewer.controls = null;

      // Get a reference to the video element
      self.video = self.panorama.getVideoElement();
      self.video.style.display = 'none';
      self.video.addEventListener('loadedmetadata', function(e) {
        self.video.currentTime = self.time
      });
      self.video.addEventListener('timeupdate', function() {
        // ビデオの現在の再生時間と総時間を取得
        var currentTime = self.video.currentTime;
        var duration = self.video.duration;
        // プログレスバーの幅を更新
        self.progress = (currentTime / duration) * 100;
      });
      self.video.currentTime = self.time
    },
    onPause() {
      this.video.pause();
      this.isPlaying = false;
    },
    async onPlay() {
      try {
        await this.video.play();
        this.isPlaying = true
      } catch(e) {
        console.log(e)
      }
    },
    onRewind() {
      this.video.currentTime = this.video.currentTime < this.step ? 0 : this.video.currentTime - this.step;
    },
    onFast() {
      this.video.currentTime = this.video.duration - this.video.currentTime < this.step ? 
        this.video.duration : this.video.currentTime + this.step;
    },
    onSeekTo(event) {
      const boundingRect = event.currentTarget.getBoundingClientRect();
      const clickX = event.clientX - boundingRect.left;
      const fullWidth = boundingRect.width;
      const percent = (clickX / fullWidth) * 100;
      this.progress = percent;
      const time = parseInt((percent / 100) * this.video.duration);
      this.video.currentTime = time;
    },
    async onCapture() {
      if(!this.viewer) return;
      const self = this
      try {
        this.is_capturing = true
        const image = new Image();
        image.src = this.viewer.renderer.domElement.toDataURL();
        let formData = new FormData();
        formData.append('file', image);
        console.log("formdata'", formData)
        const camera = this.viewer.getCamera();
        const rotation_vector = camera.rotation;  // カメラの回転情報を取得
        const rotation = {
          x: rotation_vector.x,
          y: rotation_vector.y,
          z: rotation_vector.z,
        }
        const zoom = camera.zoom;
        let latlng = this.nearsetLatLng(this.video.currentTime)
        console.log('latlng', latlng)
        latlng = latlng ? { 
          lat: latlng.lat,
          lng: latlng.lng,
        } :  { 
          lat: null,
          lng: null,
        }
        const params = {
          media_id: self.id,
          // file_str: file_str,
          playtime: self.video.currentTime.toFixed(2),
          rotation: JSON.stringify(rotation),
          zoom: zoom.toFixed(2),
          lat: latlng.lat,
          long: latlng.lng,
        }
        const { data } = await axios.post('/api/capture/file_upload', formData, {
          headers: {
              'Content-Type': 'multipart/form-data'
          }
        })
        if(data.success) {
          self.show_toast("success", data.success)
        } else {
          self.show_toast("error", data.error)
        }
      } catch(e) {
        console.log(e)
        self.show_toast()
      } finally {
        this.is_capturing  =false
      }
    }
  },
};
</script>
