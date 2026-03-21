import api from './axios';

export const getLinks = (params = {}) => {
    return api.get("/links", { params });
};

export const CreateLink = (data) => {
    return api.post("/links", data);
};

export const DeleteLink = (id) => {
    return api.delete(`/links/${id}`);
};