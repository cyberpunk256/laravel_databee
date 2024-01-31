<script setup>
import MapLayout from '@/Layouts/MapLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <MapLayout>
    <template v-slot:map>
      <div id="map" class="fix_map"></div>
    </template>
    <template v-slot:action>
      <v-btn @click="onShowCaptures" icon="mdi-cart" size="large"></v-btn>
    </template>
    <template v-slot:content>
      <v-dialog v-if="modal_type == 1 && modal_video_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <three-video-player 
            :url="modal_video_url"
            :time="modal_time"
            :nearsetLatLng="onNearestLatLng"
            :id="modal_id"
          />
        </v-card>
      </v-dialog>
      <v-dialog v-if="modal_type == 2 && modal_image_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <v-img 
              lazy-src="/empty.png"
              v-if="modal" 
              max-width="1000" 
              :src="modal_image_url"
            ></v-img>
          </div>
        </v-card>
      </v-dialog>
      <v-dialog v-if="modal_type == 3 && modal_image_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <panorama v-if="modal"  :url="modal_image_url"></panorama>
          </div>
        </v-card>
      </v-dialog>
      <v-dialog v-model="capture_modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="capture_modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <CaptureList></CaptureList>
          </div>
        </v-card>
      </v-dialog>
    </template>
  </MapLayout>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Panorama.vue';
import CaptureList from '@/Components/CaptureList.vue';

export default {
  components: { ThreeVideoPlayer, Panorama, CaptureList },
  props: ['type', 'records'],
  data() {
    return {
      map: null,
      modal: false,
      modal_type: 1, // video
      modal_video_url: null,
      modal_image_url: null,
      modal_points: [],
      modal_time: 0,
      capture_modal: false,
      // map
      bounds_sum: new L.LatLngBounds(),
      gpxOptions: {
        async: true,
        marker_options: {
            startIconUrl: false,
            endIconUrl: false,
            shadowUrl: false,
        },
        gpx_options: {
          joinTrackSegments: false
        },
        polyline_options: {
          color: 'blue', // Change the line color if needed
          weight: 12, // Set the thickness of the line
          opacity: 1,
          lineCap: 'round'
        }
      }
    };
  },
  mounted() {
    const self = this
    const map_options = {
      maxZoom: self.constant.map.max_zoom,
    }
    const init_pos = self.user.init_lat && self.user.init_long ? 
      [self.user.init_lat, self.user.init_long] :
      self.constant.map.view
    const gpxOptions = self.gpxOptions
    gpxOptions.polyline_options.weight = self.getLineWeightByZoom(
      self.constant.map.zoom, 
      self.constant.map.gpx.weight
    )
    const iconSize = self.getMarkerSizeByZoom(self.constant.map.zoom, self.constant.map.marker.size)
    const marker_icon = L.icon({
      iconUrl: self.constant.map.marker.icon, // Replace with the path to your image
      iconSize: [iconSize, iconSize], // Set the size of the icon
    });

    const map = L.map('map', map_options).setView(init_pos,self.constant.map.zoom)
      .on('zoomend', self.onZoomChange);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>',
      maxZoom: self.constant.map.max_zoom
    }).addTo(map)
    for (let i = 0; i < self.records.length; i++) {
      const record = self.records[i];
      if(record.type == 1) { // video
        const gpx_url = self.get_path_url(record.gpx_path)
        new L.GPX(gpx_url, self.gpxOptions)
          .on('loaded', function(e) {
            self.onGpxLoaded(e, record, map)
          })
      } else if(record.type == 2 || record.type == 3) {
        const coordinate = (record.image_lat && record.image_long) ? 
          [record.image_lat, record.image_long] : self.constant.map.pin
        
        const pin_marker = L.marker(coordinate, { icon: marker_icon, draggable: true })
          .addTo(map)
          .on('click', function() {
            self.onShowModal(record)
          });
        self.onFitBounds(map, coordinate);
      }
    }
    self.map = map
  },
  methods: {
    onFitBounds(map, bounds) {
      let mapBounds = map.getBounds();
      mapBounds.extend(bounds);
      map.fitBounds(mapBounds);
    },
    onGpxLoaded(e, record, map) {
      const self = this
      const gpxLayer = e.target;
      self.onFitBounds(map, gpxLayer.getBounds());
      gpxLayer.addTo(map);
      gpxLayer.on('click', function (event) {
        const layers = gpxLayer.getLayers()
        const latlngs = layers[0].getLatLngs()
        const first_latlng = latlngs[0];
        const first_moment =  moment(first_latlng.meta.time)
        self.modal_points = latlngs.map(latlng => {
          const latlng_moment = moment(latlng.meta.time)
          const latlng_time = latlng_moment.diff(first_moment, 'seconds')
          return {
            latlng: latlng,
            time: latlng_time,
          }
        });
        var target_latlng = event.latlng;
        const time = self.onNearestTime(target_latlng)
        self.onShowModal(record, time)
      });
    },
    onNearestTime(target_latlng) {
      try {
        let nearest_point = this.modal_points[0];
        this.modal_points.forEach(function (point, i) {
          if(target_latlng.distanceTo(point.latlng) < target_latlng.distanceTo(nearest_point.latlng)) {
            nearest_point = point
          }
        });
        return nearest_point.time
      } catch(e) {
        console.log(e)
        return 0
      }
    },
    onNearestLatLng(target_time) {
      try {
        let nearest_point = this.modal_points[0];
        this.modal_points.forEach(function (point, i) {
          if(Math.abs(target_time - point.time) < Math.abs(target_time - nearest_point.time) ) {
            nearest_point = point
          }
        });
        return nearest_point.latlng
      } catch(e) {
        console.log(e)
        return null
      }
    },
    onShowModal(record, time) {
      this.modal_id = record.id
      this.modal_type = record.type
      this.modal_time = time
      if(record.type == 1) {
        this.modal_video_url = record.media_path
        this.modal = true
      } else { // image, panorama
        this.modal_image_url = this.get_path_url(record.media_path)
        this.modal = true
      }
    },
    onShowCaptures() {
      this.capture_modal = true
    },
  },
};
</script>