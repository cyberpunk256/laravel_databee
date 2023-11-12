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
  watch: {
  },
  props: ['url', 'id'],
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
    initVideoPlayer() {
      const self = this
      // Create a viewer for the panorama
      this.viewer = new Viewer({
        container: this.$refs.vp_wrap,
      });

      // Create a VideoPanorama with your 360-degree video
      this.panorama = new VideoPanorama(this.url, {
        autoplay: false, // Disable auto-play for custom control handling
      });

      // Add the VideoPanorama to the viewer
      this.viewer.add(this.panorama);

      // Get a reference to the video element
      this.video = this.panorama.getVideoElement();
      this.video.addEventListener('timeupdate', function() {
          // ビデオの現在の再生時間と総時間を取得
          var currentTime = self.video.currentTime;
          var duration = self.video.duration;

          // プログレスバーの幅を更新
          self.progress = (currentTime / duration) * 100;
      });

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
        const file_str = this.viewer.renderer.domElement.toDataURL();
        const camera = this.viewer.getCamera();
        const rotation_vector = camera.rotation;  // カメラの回転情報を取得
        const rotation = {
          x: rotation_vector.x,
          y: rotation_vector.y,
          z: rotation_vector.z,
        }
        const scale = camera.zoom;
        const params = {
          media_id: self.id,
          file_str: file_str,
          playtime: self.video.currentTime.toFixed(2),
          rotation: JSON.stringify(rotation),
          zoom: scale.toFixed(2),
          lat: 35.685175,
          long: 139.7528,
        }
        console.log('params', params)
        const { data } = await axios.post('/api/capture/file_upload', params)
        if(data.success) {
          self.show_toast("success", data.success)
        } else {
          self.show_toast("error", data.error)
        }
      } catch(e) {
        console.log(e)
        self.show_toast()
      }

    }
  },
};
</script>
