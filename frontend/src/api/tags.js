import api from "./axios";

export const getTags = () => api.get("/tags");

export const attachTags = (linkId, tags) =>
  api.post(`/links/${linkId}/tags`, { tags });

export const detachTag = (linkId, tagId) =>
  api.delete(`/links/${linkId}/tags/${tagId}`);
