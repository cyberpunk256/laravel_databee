<template>
    <div>
        <div id="map" class="m_map"></div>
        <v-dialog
            v-model="modal"
            width="auto"
        >
            <v-card class="video_card">
                <v-btn icon="mdi-close" @click="modal = false" class="video_close"></v-btn>
                <three-video-player v-if="modal_video" :video="modal_video"/>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/ThreeVideoPlayer.vue';

export default {
  created () {
  },
  components: { ThreeVideoPlayer },
//   props: ['items'],
  data() {
    return {
      map: null,
      zoom: 2,
      center: [47.41322, -1.219482],
      modal: false,
      modal_video: null,
      items: [
        {
            type: 'video',
            gpx_file: "/demo.gpx",
            video_url: "/demo.mp4"
        },
      ],
      view: [36.2048, 138.2529],
      gpxOptions: {
        async: true,
        marker_options: {
          startIconUrl: '/pin-icon-start.png',
          endIconUrl:   '/pin-icon-end.png',
          shadowUrl:    '/pin-shadow.png',
        },
        gpx_options: {
            joinTrackSegments: false
        }, 
      }
    };
  },
  mounted() {
    const self = this
    self.map = L.map('map').setView(this.view,6);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    }).addTo(self.map)
    for (let i = 0; i < self.items.length; i++) {
      const item = self.items[i];
      if(item.type == 'video') {
          new L.GPX(item.gpx_file, this.gpxOptions)
            .on('loaded', self.onLoaded)
            .on('click', function() {
                self.onShowModal(item)
            });
        }
    }
  },
  methods: {
    onLoaded(e) {
      this.map.fitBounds(e.target.getBounds());
      e.target.addTo(this.map);
    },
    onShowModal(item) {
      this.modal_video = item.video_url
      this.modal = true
    }
  },
};
</script>