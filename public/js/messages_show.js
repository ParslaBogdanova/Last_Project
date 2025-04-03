document.addEventListener('DOMContentLoaded', () => {
    scrollToBottom();
    
    const messageInput = document.getElementById('messageInput');
    
    messageInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter' && !event.shiftKey) { 
            event.preventDefault();
            sendMessage();
        }
    });

    document.getElementById('fileInput').addEventListener('change', function(event) {
        const filePreviewContainer = document.querySelector('.file-preview-container');
        const filePreviewArea = document.getElementById('filePreviewArea');
        
        filePreviewArea.innerHTML = '';
    
        if (event.target.files.length > 0) {
            filePreviewContainer.style.display = 'flex';
        } else {
            filePreviewContainer.style.display = 'none';
        }
    
        Array.from(event.target.files).forEach(file => {
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
    });
    
});

function sendMessage() {
    const messageInput = document.getElementById("messageInput");
    const fileInput = document.getElementById("fileInput");
    const chatMessages = document.getElementById("chatMessages");
    const receiverId = document.querySelector('meta[name="receiver-id"]').getAttribute("content");

    const messageContent = messageInput.value.trim();
    const files = fileInput.files;


    if (!messageContent && files.length === 0) {
        console.log("No message or file to send");
        return;
    }

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
    .then(response => response.json())
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
        setTimeout(() => {
            location.reload();
        }, 1000);
    })
    
    .catch(error => console.error("Error sending message:", error));

    messageInput.value = "";

    let newFileInput = document.createElement("input");
    newFileInput.type = "file";
    newFileInput.id = "fileInput";
    newFileInput.name = "file";
    newFileInput.accept = ".jpg,.jpeg,.png,.pdf,.docx,.txt";
    newFileInput.style.display = "none";
    fileInput.replaceWith(newFileInput);

    document.getElementById("filePreviewArea").innerHTML = "";
}
window.onload = function() {
    setTimeout(() => {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }, 100);
};


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
    .catch(error => console.error('Error saving edited message:', error));
}

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
            console.error('Error deleting message');
        }
    })
    .catch(error => console.error('Error:', error));
}



