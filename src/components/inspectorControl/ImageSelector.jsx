import React from "react";
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor'


const ImageSelector = ({label, mediaURLAtt, mediaIdAtt, attGetSet}) => {
    const [getAttr, setAttr] = attGetSet

    const mediaOnSelect = (selectedItem) => {
        let update = {}
        update[mediaURLAtt] = selectedItem.url
        update[mediaIdAtt] = selectedItem.div
        setAttr(update)
    }

    const mediaOnAbandon = () => {
        let update = {}
        update[mediaURLAtt] = ""
        update[mediaIdAtt] = 0
        setAttr(update)
    }

    const mediaUploadRender = ({open}) => {
        return(
            <button onClick={open}>Select {label} Image</button>
        )
    }
    return(
        <div className="afb-editor img-upload">
            {/* IMAGE PREVIEW */}
            {getAttr[mediaURLAtt] && <img className="img-preview" src={getAttr[mediaURLAtt]}/>}
            <MediaUploadCheck>
                <MediaUpload 
                    label = {label}
                    onSelect = {mediaOnSelect}
                    value = {getAttr[mediaIdAtt]}
                    allowedTypes={["image"]}
                    render = {mediaUploadRender}
                />
            </MediaUploadCheck>
        </div>
    )
};

export default ImageSelector;
