import axios from "axios";

export const getTags = () => axios.get('/tags');

export const attachTags = (linkId, tags) =>
    axios.post(`/links/${linkId}/tags`, { tags });

export const detachTag = (linkId, tagId) =>
    axios.delete(`/links/${linkId}/tags/${tagId}`);