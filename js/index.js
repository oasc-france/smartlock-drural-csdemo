var map = L.map("map");

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: 'Data © <a href="http://osm.org/copyright">OpenStreetMap</a>',
    maxZoom: 18
}).addTo(map);

var loc = null;
devices.objects.forEach(device => {
    const staticAttributes = {};
    device.static_attributes.forEach(staticAttribute => {
        staticAttributes[staticAttribute.name] = staticAttribute;
        if (staticAttribute.name === "location") {
            loc = staticAttribute.value.split(",");
        }
    });

    var container, h1, h2, p;

    container = document.createElement("div");

    h1 = document.createElement("h1");
    h1.innerText = device.name;
    container.appendChild(h1);

    lockStatusClasses = {
        LOCKED: "text-success",
        UNLOCKED: "text-danger"
    }
    if (staticAttributes.lock_status) {
        h2 = document.createElement("h2");
        h2.innerText = staticAttributes.lock_status.value;
        h2.classList.add(lockStatusClasses[staticAttributes.lock_status.value]);
        container.appendChild(h2);

        if (staticAttributes.lock_status_date) {
            p = document.createElement("p");
            p.innerText = staticAttributes.lock_status_date.value;
            container.appendChild(p);
        }
    }

    var callback = function (e) {
        bootbox.alert({
            message: container.innerHTML,
            size: "large"
        });
    };

    new L.marker(loc).addTo(map).on("click", callback);
});

map.setView(loc, 12);
