document.addEventListener('DOMContentLoaded', () => {
    // Scroll to the bottom of the chat after the page loads
    scrollToBottom();

    const messageInput = document.getElementById('messageInput');
    
    messageInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter' && !event.shiftKey) { 
            event.preventDefault();
            sendMessage();
        }
    });

    document.getElementById('fileInput').addEventListener('change', function(event) {
        handleFilePreview(event.target.files);
    });
});


/**
 * Handles file preview by displaying images or file links in the UI.
 * 
 * This function is triggered when files are selected through the file input. 
 * It reads the file(s) and creates a preview for each file. For image files, 
 * it shows a thumbnail preview. For other file types, it provides a download 
 * link.
 * 
 * @param {FileList} files - The list of files selected by the user.
 */
function handleFilePreview(files) {
    const filePreviewContainer = document.querySelector('.file-preview-container');
    const filePreviewArea = document.getElementById('filePreviewArea');
    filePreviewArea.innerHTML = '';

    if (files.length > 0) {
        filePreviewContainer.style.display = 'flex';
    } else {
        filePreviewContainer.style.display = 'none';
    }

    Array.from(files).forEach(file => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const fileType = file.type.split('/')[0];
            const filePreviewItem = document.createElement('div');
            filePreviewItem.classList.add('file-preview-item');

            if (fileType === 'image') {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = file.name;
                img.classList.add('preview-image');
                filePreviewItem.appendChild(img);
            } else {
                const fileLink = document.createElement('a');
                fileLink.href = e.target.result;
                fileLink.download = file.name;
                fileLink.textContent = file.name;
                filePreviewItem.appendChild(fileLink);
            }

            filePreviewArea.appendChild(filePreviewItem);
        };

        reader.readAsDataURL(file);
    });
}



/**
 * Sends a message along with any attached files to the server.
 * Users don't have to send a attached file, content alone is enough
 * 
 * This function listens for the Enter key in the message input field, and when
 * pressed, it sends the content of the message along with any selected files
 * to the server using a POST request. It also updates the UI by adding the 
 * sent message to the chat.
 * 
 * @function sendMessage
 */
function sendMessage() {
    const messageInput = document.getElementById("messageInput");
    const fileInput = document.getElementById("fileInput");
    const chatMessages = document.getElementById("chatMessages");
    const receiverId = document.querySelector('meta[name="receiver-id"]').getAttribute("content");

    const messageContent = messageInput.value.trim();
    const files = fileInput.files;

    if (!messageContent && files.length === 0) {
        console.log("No message to send");
        return;
    }


    // Create a temporary message container to display while the message is being sent
    let tempMessage = document.createElement("div");
    tempMessage.classList.add("message-container", "sent");
    tempMessage.innerHTML = `
        <div class="user-name">You</div>
        <div class="message">
            ${messageContent ? `<div class="message-content">${messageContent}</div>` : ""}
            <div class="file-preview-area"></div>
        </div>
        <div class="message-time">Just now</div>
    `;
    chatMessages.appendChild(tempMessage);
    scrollToBottom();

    let formData = new FormData();
    formData.append("content", messageContent);
    formData.append("receiver_id", receiverId);

    Array.from(files).forEach(file => {
        formData.append("files[]", file);
    });

    fetch("/messages", {
        method: "POST",
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        }
    })
    .then(async response => {
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.includes("application/json")) {
        return response.json();
    } else {
        const text = await response.text();
        throw new Error("Unexpected response: " + text);
    }
})
    .then(data => {
        if (data.message) {
            tempMessage.querySelector(".message-content").innerText = data.message.content;
    
            if (data.files && data.files.length > 0) {
                let fileContainer = document.createElement("div");
                fileContainer.classList.add("message-file");
                
                data.files.forEach(file => {
                    let fileElement = document.createElement("a");
                    fileElement.href = file.file_url;
                    fileElement.download = file.file_name;
                    fileElement.textContent = file.file_name;
                    fileContainer.appendChild(fileElement);
    
                    if (file.file_name.match(/\.(jpg|jpeg|png)$/i)) {
                        let imagePreview = document.createElement("img");
                        imagePreview.src = file.file_url;
                        imagePreview.alt = file.file_name;
                        fileContainer.appendChild(imagePreview);
                    }
                });
    
                tempMessage.querySelector(".message").appendChild(fileContainer);
            }
            scrollToBottom();
        }
        
        // reload page inputs
        setTimeout(() => {
            location.reload();
        }, 1000);
    })
    .catch(error => {
        console.error("Error sending message:", error);
        alert("There is an error in your message. Please check the file you are trying to send.");
    });
}

/*reloads the page and scrolls to the bottom of the latest message. It helps, when a user sends a...
...document or image.*/
window.onload = function() {
    setTimeout(() => {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }, 100);
};



/**
 * Scrolls the chat window to the bottom so that the user can always see the latest message.
 * Useful after sending a message or when the page reloads.
 */
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const button = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (e.target === button) {
            menu.style.display = (menu.style.display === 'block' ? 'none' : 'block');
        } else {
            menu.style.display = 'none';
        }
    });
});

//----------------------EDIT MESSAGE------------------------------------

/**
 * Edits an existing message by replacing its content with an input field.
 * The button is changed to "Save" to allow saving the edited message.
 * 
 * @param {number} messageId - The ID of the message to be edited.
 */
function editMessage(messageId) {
    const messageContainer = document.getElementById(`message-${messageId}`);
    const messageContent = messageContainer.querySelector('.message-content').textContent;
    
    messageContainer.querySelector('.message-content').innerHTML =
        `<input type="text" value="${messageContent}">`;
    
    const dropdown = messageContainer.querySelector('.dropdown');
    dropdown.querySelector('button').innerText = 'Save';
    dropdown.querySelector('button').onclick = function() {
        saveEditedMessage(messageId);
    };
}

//------------------------UPDATE MESSAGE----------------------------------

/**
 * Saves the edited message by sending the new content to the server.
 * 
 * @param {number} messageId - The ID of the message to save.
 */
function saveEditedMessage(messageId) {
    const messageContainer = document.getElementById(`message-${messageId}`);
    const newContent = messageContainer.querySelector('.message-content input').value;

    fetch(`/messages/${messageId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            content: newContent
        })
    })
    .then(response => response.json())
    .then(data => {
        messageContainer.querySelector('.message-content').textContent = newContent;
        
        const dropdown = messageContainer.querySelector('.dropdown');
        dropdown.querySelector('button').innerText = '...';
        dropdown.querySelector('button').onclick = function() {
            editMessage(messageId);
        };
    })
    .catch(error => console.error('Something went wrong with update function:', error));
}

//-------------------------DELETE MESSAGE---------------------------------

/**
 * Deletes a message from the server and removes it from the UI.
 * 
 * @param {number} messageId - The ID of the message to be deleted.
 */
function deleteMessage(messageId) {
    fetch(`/messages/${messageId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    })
    .then(response => {
        if (response.ok) {
            const messageContainer = document.getElementById(`message-${messageId}`);
            messageContainer.remove();
            console.log('Message deleted');
        } else {
            console.error('Something went wrong with delete function.');
        }
    })
    .catch(error => console.error('Error:', error));
}
