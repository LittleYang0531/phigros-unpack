import os
import UnityPy

def random_name(length: int):
    import random
    import string
    return ''.join(random.choice(string.ascii_letters) for _ in range(length))

def unpack_all_assets(source_folder : str, destination_folder : str):
    os.makedirs(os.path.dirname(destination_folder), exist_ok=True)
    # iterate over all files in source folder
    for root, dirs, files in os.walk(source_folder):
        for file_name in files:
            # generate file_path
            file_path = os.path.join(root, file_name)
            # load that file via UnityPy.load
            env = UnityPy.load(file_path)

            # iterate over internal objects
            for obj in env.objects:
                
                if obj.type.name in ["TextAsset"]:
                    data = obj.read()
                    os.makedirs(os.path.join(destination_folder, obj.type.name), exist_ok=True)
                    dest = os.path.join(destination_folder, obj.type.name, data.name + "_" + random_name(8))
                    dest, ext = os.path.splitext(dest)
                    dest = dest + ".json"
                    print("Unpacking " + dest + "...")
                    with open(dest, "wb") as f:
                        f.write(data.script)

unpack_all_assets("./aa/Android/", "./assets/")